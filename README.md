# Portfolio-API-PHP
Backend PHP I use with my portfolio website for sending emails. This project works alongside my Portfolio-API.

## Technologies Used:
* PHP
* PHPMailer
* Composer

## Local Testing:
Run command via terminal: 
```bash
php -S localhost:8000 -t public
```

## Base URL:
> [!NOTE]  
> For production usage replace `DOMAIN` with your domain name (example: ``myWebsite.com``).

* Production: ```http://DOMAIN/portfolio-api/public```
* LocalHost: ```http://localhost:8000```

### Port:
VPS Server: ```80```
LocalHost: ```8000```

## Status Check (Visit URL):
**General Service**
* Production: ```http://VPS-IP-/DOMAIN/public```
* LocalHost: ``` localhost:8000```
* **Response:**
  * Online: ```{"message":"PHP Email API is running!"}```
  * Offline: ```ERR_CONNECT_REFUSED``` or ```404 Not Found``` or ```This site can't be reached```

**Email Service**
* VPS Server: ```http://DOMAIN/portfolio-api/public/email.php```
* LocalHost: ``` localhost:8000/email.php```
* **Response**
  * Online: ```{"error":"Invalid request method."}```
  * Offline: ```ERR_CONNECT_REFUSED``` or ```404 Not Found``` or ```This site can't be reached```


## Endpoints
```POST /email.php```
* sends an email with the provided details.

**Request Example (cURL)**
* **LocalHost:**
``` sh
curl -X POST http://localhost:8000/email.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"johndoe@example.com","message":"Hello!"}'

```
* **Production:**
``` sh
curl -i -X POST https://jacobjmiller.com/portfolio-api/public/email.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"johndoe@example.com","message":"Hello!"}'

```

**Request Body (JSON)**
``` json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "message": "Hello, this is a test message!"
}
```
**Response**
* Success:
``` json
{
  "success": "Message sent successfully!"
}
```
* Failure:
``` json
{
  {
  "error": "Message could not be sent. Error: [error details]"
}
```

## Related Projects:
* [Portfolio API](https://github.com/JMiller7334/portfolio-api)

## Links:
* [JacobJMiller.com](https://JacobJMiller.com)
