<?php

class OrdersController extends Controller{

    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->model = new Order();
    }

    //вывод информации о доставке
    public function index(){
    }

    //добавить товар в корзину (Session)
    public function add_order_item() {
        $flag = false;
        $arrayItems = Session::get('cart');
        $key = App::getRouter()->getParams()[0];
        if (count($arrayItems) != 0) {
            foreach ($arrayItems as $inner_key => $value) {
                if ($key == $inner_key) {
                    $arrayItems[$key] += 1;
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                $arrayItems[$key] = 1;
            }
        } else {
            $arrayItems[$key] = 1;
        }
        Session::set('cart', $arrayItems);
        Router::redirect('/products/');
    }

    //удалить товар из корзины (Session)
    public function delete_order_item() {
        $flag = false;
        $arrayItems = Session::get('cart');
        $key = App::getRouter()->getParams()[0];
        if (count($arrayItems) != 0) {
            foreach ($arrayItems as $inner_key => $value) {
                if ($key == $inner_key) {
                    if($arrayItems[$key]>1) {
                        $arrayItems[$key] -= 1;
                    } else {
                        unset($arrayItems[$key]);
                    }
                    $flag = true;
                    break;
                }
            }
            if (!$flag) {
                unset($arrayItems[$key]);
            }
        } else {
            unset($arrayItems[$key]);
        }
        Session::set('cart', $arrayItems);
        Router::redirect('/orders/');
    }

    //добавить заказ от клиента в базу данных
    public function add_order() {
        if ($_POST && isset($_POST['address']) && isset($_POST['house'])
            && isset($_POST['hour']) && isset($_POST['minute'])) {
            //добавляем адрес клиента в базу
            $address = $_POST['address'].", ".$_POST['house'];
            (new User())->addUserAddress(Session::get('login'), $address);
            //добавляем заказ
            $cart = Session::get('cart');
            $login = Session::get('login');
            $driver_login = $this->model->addNewOrder($_POST['hour'], $_POST['minute'], $cart, $login);
            Session::delete('cart');
        }
        Router::redirect('/products/');
    }

    //обновить статус заказа
    public function order_status_update()
    {
        if ($_POST) {
            $order_status = $_POST['selectvalue1'];
            $order = $this->model->getActiveOrderForDriver(Session::get('login'));
            $order_id = $order[0]["id"];
            $this->model->addOrderStatus($order_id, $order_status);
        }
        Router::redirect('/products/');
    }
}