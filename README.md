
## Description of the problem .

- we need to  add multiple items to the cart with possibility to update,delete,add items and destroy cart if we need .
- display a bill after adding items in different cases :

1- items cart doesn't have discount or offers .

2- items cart have  offers .

 3- convert currencies case .

## solution


##### Used Technologies :
1 - PHP & Laravel Framework

2- API

3- Sql & Mysql Server

4- Php unit tests (Feature and Unit).
##### Architecture & Patterns :
1- MVC

2- Restful

3- OOP

## Installation

1. Clone the repo and `cd` into it
2. Run `composer install` command .
1. Rename or copy `.env.example` file to `.env`
1. Run `php artisan key:generate` command.
1. Copy key and paste in `.env` file Specifically `APP_KEY`.
1. Set your database credentials in your `.env` file
1. Run `php artisan migrate:fresh` command. This will migrate the database.
1. Run `php artisan serve` command or use Laravel Valet or Laravel Homestead .
1. Use `localhost:8000` in This Postman Collection

API Route: http://localhost:8000/api/cart
##### Postman link : https://documenter.getpostman.com/view/6483323/TVYKbH62 
## unit Test

1- tests/Feature/CartControllerTest.php

2  tests/Unit/CartTraitTest.php
