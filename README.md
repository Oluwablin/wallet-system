### WALLET SYSTEM

A simple wallet system application that uses paystack as a payment gateway.

## Project Description

The project is about a simple wallet system application that uses paystack as a payment gateway.built with [Laravel 8](https://laravel.com) and [Paystack API](https://paystack.com/docs/api/). The features of this project include

1.  Fund Wallet (create and read).
2.  Balance Enquiry.
3.  Transaction History. 

## Project Setup

### Cloning the GitHub Repository

Clone the repository to your local machine by running the terminal command below.

```bash
git clone https://github.com/Oluwablin/wallet-system
```

### Setup Database

Create a MySQL database and note down the required connection parameters. (DB Host, Username, Password, Name)

### Install Composer Dependencies

Navigate to the project root directory via terminal and run the following command.

```bash
composer install
```

### Create a copy of your .env file

Run the following command

```bash
cp .env.example .env
```

This should create an exact copy of the .env.example file. Name the newly created file .env and update it with your local environment variables (database connection info and others).

### Generate an app encryption key

```bash
php artisan key:generate
```

### Generate a jwt encryption secret key

```bash
php artisan jwt:secret
```

### Migrate the database

```bash
php artisan migrate
```

### Add the required environment variables.

PAYSTACK_SECRET_KEY

### API DOCUMENTATION.

Examples of requests and the response for each endpoint can be found [here](https://documenter.getpostman.com/view/11139475/Tzz7PJPg)
