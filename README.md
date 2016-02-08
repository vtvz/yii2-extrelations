Yii2 Relations Extension
========================

Расширение, которое позволяет легко создавать связи между различными ActiveRecord моделями.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist vtvz/yii2-relations
```

or add

```json
"vtvz/yii2-relations": "*"
```

to the require section of your composer.json.

Usage
-----

Класс vtvz\relations\Relations является поведением.

Поведение поддерживает все виды связей и позволяет удобно их настраивать.

Пример:

```php
<?php
namespace common\models;

use vtvz\relations\Relations;

class Order extends Model
{
    public function behaviors()
    {
        return [
            'relations' => [
                'class' => Relations::className(),
                'relations' => [
                    /* many to many relation type */
                    'items' => [
                        'model' => Item::className(),
                        'link' => ['id' => 'itemId'],
                        'viaTable' => 'order_has_item',
                        'viaLink' => ['orderId' => 'id'],
                        'inverseOf' => 'orders',
                    ],
                    /* one (customer) to many (orders) relation type */
                    'customer' => [
                        'model' => Customer::className(),
                        'link' => ['id' => 'customerId'],
                        'type' => 'one',
                    ],
                    /* one to one relation type with delete */
                    'orderInfo' => [
                        'model' => OrderInfo::className(),
                        'link' => ['id' => 'id'],
                        'type' => 'one',
                        'delete' => true,
                    ],
                    /* many (somethings) to one (order) relation type.
                    All somethings deletes with its order */
                    'somethings' => [
                        'model' => Something::className(),
                        'link' => ['orderId' => 'id'],
                        'type' => 'many',
                    ]
                ],
            ],
        ];
    }
}
?>
```

Примечание
----------

Расширение находится на стадии ранней разработки. Нужна помощь в покрытии расширения тестами и создании нормальной документации.
