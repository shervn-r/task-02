# Task 2 - URL-Shortner
-----

### Installation

1. Run `compose install` in order to install required packages.
2. Run below command to serve the project locally.
    ```
   php -S localhost:8000 -t ./public
   ```

### Configuration

1. The root directory of application contains a `.env.example`. Rename the `.env.example` file to `.env`.
2. Set the application key (`APP_KEY`) to a randomly generated 32 characters long string in `.env` file. 
**If the application key is not set, the user encrypted data will not be secure!**  


### Authentication

For the authentication, I decided to go with JWT.

1. JWT works with a secret key. Add a 30 characters random string to the `JWT_SECRET` as:
    ```
   JWT_SECRET=uflWMsDJdKFLge6pjX0qLBJvdDUHJK
   ```

### Database

1. Create a SQLite database by using the `touch database/database.sqlite` command. Configure the environment
 variables to point to this newly created by using the database's absolute path and commenting unnecessary 
 variables out.
    ```
   DB_CONNECTION=sqlite
   #DB_HOST=127.0.0.1
   #DB_PORT=3306
   DB_DATABASE=/absolute/path/to/database.sqlite
   #DB_USERNAME=homestead
   #DB_PASSWORD=secret
   ```

2. Run all of the migration files by executing `migrate` Artisan command:
    ```
   php artisan migrate
   ```
   

### Queues

This project makes use of Redis queue driver to have faster redirection and defer processing clicks task. So
make sure you have redis installed on your operating system. If not you can visit 
[Redis Download page](https://redis.io/download) to have installed.

1. Change Redis-related environment variable in `.env` file as stated below:
    ```
   QUEUE_CONNECTION=redis
   ```
   and 
    ```
   REDIS_CLIENT=predis
   ```
2. Run `redis-server` in your terminal to start Redis.
3. Run Queue worker so as to process the jobs (here we have only one job and that is named `ProcessClick`). 
 You may run the worker using the `queue:work` command:
    ```
   php artisan queue:work
   ```

### Postman Collection
Follow this [Link](https://www.getpostman.com/collections/2f5f8d8439ec9e66ea40) to access API collection in 
Postman or this [Published Documents Link](https://documenter.getpostman.com/view/3102731/SzS2yp7F?version=latest) in 
order to access it in your browser.
