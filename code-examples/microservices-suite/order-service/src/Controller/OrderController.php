<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Client $httpClient = new Client(['timeout' => 5])
    ) {}

    #[Route('/health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse([
            'service' => 'order-service',
            'status' => 'healthy',
            'timestamp' => time()
        ]);
    }

    #[Route('/orders', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['customer_name']) || !isset($data['items']) || !isset($data['total_amount'])) {
            return new JsonResponse(['error' => 'Missing required fields'], 400);
        }

        $order = new Order();
        $order->setCustomerName($data['customer_name']);
        $order->setItems(json_encode($data['items']));
        $order->setTotalAmount($data['total_amount']);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        try {
            $paymentResponse = $this->httpClient->post($_ENV['PAYMENT_SERVICE_URL'] . '/payments', [
                'json' => [
                    'order_id' => $order->getId(),
                    'amount' => $order->getTotalAmount()
                ]
            ]);
            $paymentData = json_decode($paymentResponse->getBody()->getContents(), true);
            $order->setPaymentId($paymentData['id']);
            $order->setStatus('payment_processed');

            $kitchenResponse = $this->httpClient->post($_ENV['KITCHEN_SERVICE_URL'] . '/kitchen/prepare', [
                'json' => [
                    'order_id' => $order->getId(),
                    'items' => $data['items']
                ]
            ]);
            $kitchenData = json_decode($kitchenResponse->getBody()->getContents(), true);
            $order->setKitchenId($kitchenData['id']);
            $order->setStatus('preparing');

            $deliveryResponse = $this->httpClient->post($_ENV['DELIVERY_SERVICE_URL'] . '/deliveries', [
                'json' => [
                    'order_id' => $order->getId(),
                    'customer_name' => $order->getCustomerName()
                ]
            ]);
            $deliveryData = json_decode($deliveryResponse->getBody()->getContents(), true);
            $order->setDeliveryId($deliveryData['id']);
            $order->setStatus('out_for_delivery');

            $this->entityManager->flush();
        } catch (\Exception $e) {
            $order->setStatus('failed');
            $this->entityManager->flush();
            return new JsonResponse(['error' => 'Service communication failed: ' . $e->getMessage()], 500);
        }

        return new JsonResponse([
            'id' => $order->getId(),
            'customer_name' => $order->getCustomerName(),
            'items' => json_decode($order->getItems(), true),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'payment_id' => $order->getPaymentId(),
            'kitchen_id' => $order->getKitchenId(),
            'delivery_id' => $order->getDeliveryId(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s')
        ], 201);
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function getOrder(int $id): JsonResponse
    {
        $order = $this->entityManager->getRepository(Order::class)->find($id);

        if (!$order) {
            return new JsonResponse(['error' => 'Order not found'], 404);
        }

        return new JsonResponse([
            'id' => $order->getId(),
            'customer_name' => $order->getCustomerName(),
            'items' => json_decode($order->getItems(), true),
            'total_amount' => $order->getTotalAmount(),
            'status' => $order->getStatus(),
            'payment_id' => $order->getPaymentId(),
            'kitchen_id' => $order->getKitchenId(),
            'delivery_id' => $order->getDeliveryId(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/orders', methods: ['GET'])]
    public function listOrders(): JsonResponse
    {
        $orders = $this->entityManager->getRepository(Order::class)->findAll();

        $data = array_map(function (Order $order) {
            return [
                'id' => $order->getId(),
                'customer_name' => $order->getCustomerName(),
                'items' => json_decode($order->getItems(), true),
                'total_amount' => $order->getTotalAmount(),
                'status' => $order->getStatus(),
                'payment_id' => $order->getPaymentId(),
                'kitchen_id' => $order->getKitchenId(),
                'delivery_id' => $order->getDeliveryId(),
                'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }, $orders);

        return new JsonResponse($data);
    }
}
