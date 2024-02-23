# API GitHub

This project is an API that handles a carpool database.

## Getting Started

To get started with this project, follow the steps below:

1. Clone the repository.
2. Install the required dependencies using `composer install`.
3. Configure the database connection in the `.env` file.
4. Run the database migrations using `php bin/console doctrine:migrations:migrate`.
5. Load the admin fixture pour le compte administrateur `php bin/console doctrine:fixtures:load`
6. Start the development server using `symfony serve`.

## Default Admin User

To access the API as an admin user, use the following credentials:

- Username: admin
- Password: admin

> ⚠️ **Warning:** It is recommended to change the default admin password in a production environment.

# Format datetime in URL

The datetime format in the URL is in the format: Y-m-d H:i

## Error Handling

In this project, error handling is centralized using a Symfony event listener [API Exception handler](./src/EventListener/ExceptionListener.php). The listener listens for any exceptions thrown during the execution of the API endpoints and handles them accordingly.

### How it works

1. When an exception occurs within the API endpoint code, it is thrown.
2. The Symfony event listener catches the exception and performs the necessary error handling logic.
3. The listener generates an appropriate error response, which includes the error message and status code.
4. The error response is returned to the client.

By centralizing error handling in a listener, we can ensure consistent error responses across all API endpoints and easily customize the error handling logic as needed.

## API Documentation

For detailed documentation on the API endpoints and how to use them, refer to the [API Documentation](./docs/api.md) file.

## Contributing

If you would like to contribute to this project, please follow the guidelines outlined in the [CONTRIBUTING.md](./CONTRIBUTING.md) file.

## License

This project is licensed under the [MIT License](./LICENSE).

