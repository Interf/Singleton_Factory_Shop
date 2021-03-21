<?php

class Prototype
{
    protected static $instance = [];
    protected $id = null;

    protected function __construct()
    {
    }

    /**
     * @param int $id
     * @param string $className
     * @return Prototype
     * @throws Exception
     */
    public static function getInstance(int $id, string $className): Prototype
    {
        $id = (int)$id;
        $className = htmlspecialchars($className);

        if (!$id || !$className) {
            throw new Exception("Error");
        }

        if (self::$instance[$className] == null || self::$instance[$className][$id] == null) {
            echo "Class Created:  $className <br>";
            self::$instance[$className][$id] = new $className;
            self::$instance[$className][$id]->setId($id);
        }

        return self::$instance[$className][$id];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    protected function setId(int $id): void
    {
        $this->id = $id;
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }
}


class User extends Prototype
{
    /**
     * @param int $id
     * @param string $class
     * @return Prototype
     * @throws Exception
     */
    public static function getInstance(int $id, string $class = __CLASS__): Prototype
    {
        return parent::getInstance($id, __CLASS__);
    }
}

class Products
{
    protected static $instance = null;
    protected static $productList = [];

    protected function __construct()
    {
    }

    /**
     * @return Products
     */
    public static function getInstance() : Products
    {
        if (self::$instance == null) {
            echo "Prod created <br>";
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Получить массив товаров
     *
     * @return array
     */
    public function getProducts(): array
    {
        return self::$productList;
    }

    /**
     * Заполнить массив товаров
     *
     * @param array $products
     * @return void
     */
    public function fillProducts(array $products) : void
    {
        foreach ($products as $arItem) {
            self::$productList[$arItem['ID']] = [
                'ID' => $arItem['ID'],
                'NAME' => $arItem['NAME'],
                'QUANTITY' => $arItem['QUANTITY']
            ];
        }
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }
}

class Cart extends Prototype
{
    protected static $userId = [];
    protected static $productList = [];

    /**
     * @param int $id
     * @param string $className
     * @return Prototype
     * @throws Exception
     */
    public static function getInstance(int $id, string $className = __CLASS__): Prototype
    {
        return parent::getInstance($id, $className);
    }

    /**
     * @param User $user
     * @throws Exception
     * @return void
     */
    public function setUserId(User $user) : void
    {
        $userId = $user->getId();
        if (array_search($userId, self::$userId) !== false) {
            throw new Exception('User already added');
        }

        self::$userId[self::getId()] = $userId;
    }

    /**
     * Положить товар в корзину
     *
     * @param int $prodId
     * @param int $quantity
     * @throws Exception
     * @return void
     */
    public function addProduct(int $prodId, int $quantity = 1) : void
    {
        $products = Products::getInstance()->getProducts();
        $prodList = array_column($products, "QUANTITY", "ID");

        if (self::$productList[self::getUserId()][$prodId]['ID'] == $prodId) {

            self::$productList[self::getUserId()][$prodId]['QUANTITY'] += $quantity;

        } else {
            self::$productList[self::getUserId()][$prodId] = [
                'ID' => $prodId,
                'QUANTITY' => $quantity
            ];
        }

        if ($prodList[$prodId] < self::$productList[self::getUserId()][$prodId]['QUANTITY']) {
            throw new Exception("Error quantity");
        }

    }

    /**
     * Удалить товар из корзины
     *
     * @param int $prodId
     * @return void
     */
    public function delProd(int $prodId) : void
    {
        unset(self::$productList[self::getUserId()][$prodId]);
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return self::$userId[self::getId()];
    }

    /**
     * @return array
     */
    public function getCart() : array
    {
        return self::$productList[self::getUserId()];
    }
}

class Order
{
    protected static $instance = null;
    protected static $orderList = [];
    protected $userId = null;
    protected $cartList = null;

    protected function __construct()
    {
    }

    /**
     * @return Order
     */
    public static function getInstance() : Order
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @param Cart $cart
     * @return void
     */
    public function setOrder(Cart $cart) : void
    {
        $this->userId = $cart->getUserId();
        $this->cartList = $cart->getCart();

        self::$orderList[$this->userId] = $this->cartList;
    }

    /**
     * @return array
     */
    public function getOrder() : array
    {
        return self::$orderList[$this->userId];
    }

    protected function __clone()
    {
    }

    protected function __wakeup()
    {
    }
}

$user1 = User::getInstance(1);
$user2 = User::getInstance(2);

$arProd = [
    [
        'ID' => 1,
        'NAME' => 'Обои',
        'QUANTITY' => 11
    ],
    [
        'ID' => 2,
        'NAME' => 'Стул',
        'QUANTITY' => 3
    ],
    [
        'ID' => 3,
        'NAME' => 'Стол',
        'QUANTITY' => 13
    ],
];
$products = Products::getInstance();
$products->fillProducts($arProd);

xdebug_var_dump($products->getProducts());

$cart1 = Cart::getInstance(2);
$cart1->setUserId($user1);
$cart1->addProduct(1);
$cart1->addProduct(2, 1);
$cart1->addProduct(2, 2);

xdebug_var_dump($cart1->getCart());

$cart1->delProd(1);

xdebug_var_dump($cart1->getCart());

$cart2 = Cart::getInstance(3);
$cart2->setUserId($user2);
$cart2->addProduct(1, 11);

xdebug_var_dump($cart2->getCart());

$order1 = Order::getInstance($cart1);
$order1->setOrder($cart1);
xdebug_var_dump($order1->getOrder());

$order2 = Order::getInstance();
$order2->setOrder($cart2);
xdebug_var_dump($order2->getOrder());
