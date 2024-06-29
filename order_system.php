<?php

namespace RefactoringGuru\Strategy\RealWorld;

class OrderController
{
    public function post(string $url, array $data)
    {
        echo "<p>Controller: POST request to $url with " . json_encode($data) . "</p>";

        $path = parse_url($url, PHP_URL_PATH);

        if (preg_match('#^/orders?$#', $path, $matches)) {
            $this->postNewOrder($data);
        } else {
            echo "<p>Controller: 404 page</p>";
        }
    }

    public function get(string $url): void
    {
        echo "<p>Controller: GET request to $url</p>";

        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $data);

        if (preg_match('#^/orders?$#', $path, $matches)) {
            $this->getAllOrders();
        } elseif (preg_match('#^/order/([0-9]+?)/payment/([a-z]+?)(/return)?$#', $path, $matches)) {
            $order = Order::get($matches[1]);
            $paymentMethod = PaymentFactory::getPaymentMethod($matches[2]);

            if (!isset($matches[3])) {
                $this->getPayment($paymentMethod, $order, $data);
            } else {
                $this->getPaymentReturn($paymentMethod, $order, $data);
            }
        } else {
            echo "<p>Controller: 404 page</p>";
        }
    }

    public function postNewOrder(array $data): void
    {
        $order = new Order($data);
        echo "<p>Controller: Created the order #{$order->id}.</p>";
    }

    public function getAllOrders(): void
    {
        echo "<p>Controller: Here's all orders:</p>";
        echo "<table border='1'>
                <tr>
                    <th>Order ID</th>
                    <th>Email</th>
                    <th>Product</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>";

        foreach (Order::get() as $order) {
            echo "<tr>
                    <td>{$order->id}</td>
                    <td>{$order->email}</td>
                    <td>{$order->product}</td>
                    <td>{$order->total}</td>
                    <td>{$order->status}</td>
                  </tr>";
        }

        echo "</table>";
    }

    public function getPayment(PaymentMethod $method, Order $order, array $data): void
    {
        $form = $method->getPaymentForm($order);
        echo "<p>Controller: here's the payment form:</p>";
        echo $form;
    }

    public function getPaymentReturn(PaymentMethod $method, Order $order, array $data): void
    {
        try {
            if ($method->validateReturn($order, $data)) {
                echo "<p>Controller: Thanks for your order!</p>";
                $order->complete();
            }
        } catch (\Exception $e) {
            echo "<p>Controller: got an exception (" . $e->getMessage() . ")</p>";
        }
    }
}

class Order
{
    private static $orders = [];

    public static function get(int $orderId = null)
    {
        if ($orderId === null) {
            return static::$orders;
        } else {
            return static::$orders[$orderId];
        }
    }

    public function __construct(array $attributes)
    {
        $this->id = count(static::$orders);
        $this->status = "new";
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
        static::$orders[$this->id] = $this;
    }

    public function complete(): void
    {
        $this->status = "completed";
        echo "<p>Order: #{$this->id} is now {$this->status}.</p>";
    }
}

class PaymentFactory
{
    public static function getPaymentMethod(string $id): PaymentMethod
    {
        switch ($id) {
            case "cc":
                return new CreditCardPayment();
            case "paypal":
                return new PayPalPayment();
            default:
                throw new \Exception("Unknown Payment Method");
        }
    }
}

interface PaymentMethod
{
    public function getPaymentForm(Order $order): string;
    public function validateReturn(Order $order, array $data): bool;
}

class CreditCardPayment implements PaymentMethod
{
    static private $store_secret_key = "swordfish";

    public function getPaymentForm(Order $order): string
    {
        $returnURL = "https://our-website.com/" .
            "order/{$order->id}/payment/cc/return";

        return <<<FORM
<form action="https://my-credit-card-processor.com/charge" method="POST">
    <input type="hidden" id="email" value="{$order->email}">
    <input type="hidden" id="total" value="{$order->total}">
    <input type="hidden" id="returnURL" value="$returnURL">
    <input type="text" id="cardholder-name">
    <input type="number" id="credit-card">
    <input type="number" id="expiration-month">
    <input type="number" id="expiration-year">
    <input type="number" id="cvv">
    <button type="submit">Pay</button>
</form>
FORM;
    }

    public function validateReturn(Order $order, array $data): bool
    {
        if ($data['key'] != md5($order->id . static::$store_secret_key)) {
            throw new \Exception("Payment failed.");
        }

        if (!$data['success'] || $data['success'] == 'false') {
            throw new \Exception("Payment failed.");
        }

        return true;
    }
}

class PayPalPayment implements PaymentMethod
{
    public function getPaymentForm(Order $order): string
    {
        $returnURL = "https://our-website.com/" .
            "order/{$order->id}/payment/paypal/return";

        return <<<FORM
<form action="https://paypal.com/charge" method="POST">
    <input type="hidden" id="email" value="{$order->email}">
    <input type="hidden" id="total" value="{$order->total}">
    <input type="hidden" id="returnURL" value="$returnURL">
    <button type="submit">Pay with PayPal</button>
</form>
FORM;
    }

    public function validateReturn(Order $order, array $data): bool
    {
        if (!$data['success'] || $data['success'] == 'false') {
            throw new \Exception("Payment failed.");
        }

        return true;
    }
}

function clientCode(OrderController $controller)
{
    $controller->post("/orders", [
        "email" => "john@example.com",
        "product" => "ABC Cat Food (XL)",
        "total" => 9.95,
    ]);

    $controller->post("/orders", [
        "email" => "john@example.com",
        "product" => "XYZ Cat Litter (XXL)",
        "total" => 19.95,
    ]);

    $controller->get("/orders");

    $controller->get("/order/1/payment/paypal");

    $controller->get("/order/1/payment/paypal/return?key=123&success=true");
}

$controller = new OrderController();
clientCode($controller);
