# API Documentation: Newsletter Subscription

## Introduction

This documentation outlines the usage of the **Newsletter Subscription API**. Front-end developers can use this API to allow users to subscribe to a newsletter by submitting their email addresses. The API ensures that only valid and unique email addresses are accepted and stored for the newsletter mailing list.

### **Base URL:**

```
https://n-tawasull.sa/api
```

---

## API Endpoint

### 1. **Subscribe to Newsletter**

* **Endpoint:** `POST /api/subscribe`
* **Description:** This endpoint allows users to subscribe to the newsletter by providing their email address. The email is validated to ensure it is unique and properly formatted before being added to the subscription list.

#### Request Body:

```json
{
  "email": "user@example.com"
}
```

* **email** (required): The email address of the user wishing to subscribe to the newsletter.

#### Example Request:

```json
{
  "email": "john.doe@example.com"
}
```

#### Example Response (Success):

```json
{
  "code": 200,
  "status": "OK",
  "message": "تم الاشتراك بنجاح! شكراً لانضمامك إلينا. سنوافيك بأحدث الأخبار قريبًا."
}
```

* **code:** The HTTP status code indicating the result of the request (`200 OK` for success).
* **status:** The status of the request (e.g., `OK`).
* **message:** A user-friendly message confirming that the subscription was successful.

#### Example Response (Validation Error):

```json
{
  "code": 422,
  "status": "Unprocessable Entity",
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  },
  "message": "أوه! يبدو أن هذا البريد الإلكتروني قد تم الاشتراك به بالفعل أو غير صالح."
}
```

* **code:** The HTTP status code for validation errors (`422 Unprocessable Entity`).
* **status:** The status of the request (e.g., `Unprocessable Entity`).
* **errors:** An object containing validation error messages for the email field.
* **message:** A user-friendly error message indicating why the subscription request failed.

#### Example Response (Internal Server Error):

```json
{
  "code": 500,
  "status": "Internal Server Error",
  "message": "حدث خطأ غير متوقع أثناء الاشتراك. لكن لا داعي للقلق، سنقوم بحل المشكلة قريباً."
}
```

* **code:** The HTTP status code for server errors (`500 Internal Server Error`).
* **status:** The status of the request (e.g., `Internal Server Error`).
* **message:** A generic error message indicating an unexpected issue occurred.

### Error Handling

#### Common Error Responses:

* **422 Unprocessable Entity:** The email provided is either invalid or already subscribed.

  * The error message will detail the specific issue (e.g., invalid email format or duplicate subscription).
* **500 Internal Server Error:** An unexpected error occurred on the server during the subscription process. This typically happens if there is a system failure or an issue with the database.

### Request Validation

The request body is validated to ensure:

* **Email format:** The email must be a valid email address.
* **Uniqueness:** The email must not already be subscribed to the newsletter.

If any of these validation checks fail, the response will return a `422` status with specific error details.

### Subscription Confirmation

Once a user successfully subscribes, their email will be stored in the system along with the timestamp of when the subscription occurred. The user will receive a confirmation message indicating successful subscription.
