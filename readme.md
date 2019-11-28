## Given problem statement-

1)Must be a RESTful HTTP API listening to port 8080.

2)The API must implement 3 endpoints with path, method, request and response body as specified
  -One endpoint to create an order 
    -To create an order, the API client must provide an origin and a destination.
    -The API responds an object containing the distance and the order ID.
  -One endpoint to take an order.
    -An order can only be taken once.
    -An error response should be returned if a client tries to take an order that is already taken.
  -One endpoint to list orders.

4)The request input should be validated before processing. The server should return proper error response in case validation fails.

5)All responses must be in json format no matter in success or failure situations.


## Solution

**Software used-** 
- PHP v7.1.33. - For backend
- MySQL 10.4.8. - For database
- Lumen v5.8. - Laravel Lumen is a PHP micro-framework for api 
- Docker - For container service
- Apache 2 - For web server
- PHPUnit - For unit and integation testing
- Swagger -  For swagger json 
- xdebug  For code coverage


**Project setup-**

1) Cloning the project
``` bash
https://github.com/naman-srivastav/task.git
```

2)Setting up the keys for google map api and database credential under task/.env.

**task/.env**
``` bash
GOOGLE_MAP_KEY=
```


3)Assumed docker is all setup to the machine.So,execute the shell script on terminal.

``` bash
cd task/
sh start.sh or bash start.sh
```

## To check code coverage ,open following url

``` bash
    `http://localhost:8080/code-coverage/`
```

## To check swagger json,open following url

``` bash
    `http://localhost:8080/swagger`
```


#### Running Test cases...

## To Perform all test cases
With Docker
``` bash
docker exec orders_php ./vendor/bin/phpunit
```

## To Perform Unit test cases
With Docker
``` bash
docker exec orders_php ./vendor/bin/phpunit ./tests/Unit
```


## To Perform Integration test cases
With Docker
``` bash
docker exec orders_php ./vendor/bin/phpunit ./tests/Integration
```

## Api Endpoint Reference Documentation


#### Place order

  - Description: Create/Post a new Order.
  - Method: `POST`
  - URL path: `http://localhost:8080/orders`
  - URL endpoint: `/orders`
  - Content-Type: `application/json`
  - Request body:

    ```
    {
        "origin": ["START_LATITUDE", "START_LONGTITUDE"],
        "destination": ["END_LATITUDE", "END_LONGTITUDE"]
    }
    ```
    - Example
    ```
    {
        "origin": ["28.644800", "77.308601"],
        "destination": ["19.076090", "72.877426"]
    }
    ```

  - Response:

    Header: `HTTP 200`
    Body:
      ```
      {
          "id": <order_id>,
          "distance": <total_distance>,
          "status": "UNASSIGNED"
      }
      ```
    or

    Header: `HTTP <HTTP_CODE>`
    Body:

      ```
      {
          "error": "ERROR_DESCRIPTION"
      }
      ```
      ```
        Code                    Description
        - 200                   successful operation
        - 400                   Bad Request
        - 422                   Request Body Validation Error
        - 405                   Method Not Allowed
        - 500                   Internal Server Error    


#### Take order

  - Description: Update/take a new Order.
  - Method: `PATCH`
  - URL path: `http://localhost:8080/orders/:id`
  - URL endpoint: `/orders/:id`
  - Content-Type: `application/json`
  - Request body:
    ```
    {
        "status": "TAKEN"
    }
    ```
  - Response:
    Header: `HTTP 200`
    Body:
      ```
      {
          "status": "SUCCESS"
      }
      ```
    or

    Header: `HTTP <HTTP_CODE>`
    Body:
      ```
      {
          "error": "ERROR_DESCRIPTION"
      }
      ```

      ```
        Code                    Description
        - 200                   successful operation
        - 400                   Bad Request
        - 405                   Method Not Allowed
        - 422                   Validation Error
        - 406                   Invalid ID
        - 409                   Order Already Taken
        - 500                   Internal Server Error    


#### Order list

  - Description: List/get Order List.
  - Method: `GET`
  - URL path: `http://localhost:8080/orders`
  - URL endpoint: `/orders`
  - Content-Type: `application/json`
  - Response:
    Header: `HTTP 200`
    Body:
      ```
      [
          {
              "id": <order_id>,
              "distance": <total_distance>,
              "status": <ORDER_STATUS>
          },
          ...
      ]
      ```

    or

    Header: `HTTP <HTTP_CODE>` Body:

    ```
    {
        "error": "ERROR_DESCRIPTION"
    }
    ```

    ```
    Code                    Description
    - 200                   Successful operation
    - 400                   Bad Request
    - 422                   Validation Error
    - 500                   Internal Server Error    


