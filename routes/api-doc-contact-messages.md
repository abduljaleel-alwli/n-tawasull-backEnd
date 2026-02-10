# API Documentation: Contact Messages

## Introduction

This documentation describes how front-end developers can interact with the Contact Messages API. The API allows users to submit contact messages, which can include optional attachments. The submission is validated and stored, and notifications are sent to administrators. The API provides a single endpoint for submitting contact messages.

### **Base URL:**

```
https://n-tawasull.sa/api
```

---

## API Endpoints

### 1. **Submit a Contact Message**

* **Endpoint:** `POST /api/contact-messages`
* **Description:** This endpoint is used to submit a contact message. The request should include the user’s name, email, message, project type, and services. Additionally, the user may attach a file (e.g., PDF, images, documents). The request will be validated, the message will be stored in the database, and notifications will be sent to administrators.

#### Request Body Like:

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "phone": "1234567890",  
  "message": "I am interested in your services and would like more details.",
  "project_type": "Website Development",
  "services": ["Design", "Development"],
  "attachment": (optional, file upload)
}
```

* **name** (required): The name of the person sending the message.
* **email** (required): The email address of the sender.
* **phone** (optional): The phone number of the sender.
* **message** (required): The content of the message.
* **project_type** (required): A description of the type of project the sender is inquiring about.
* **services** (required): A list of services that the sender is interested in. Each service is a string.
* **attachment** (optional): A file attached to the message (can be one of the following formats: pdf, jpg, jpeg, png, doc, docx).

#### Example Request:

```json
{
  "name": "Jane Smith",
  "email": "jane.smith@example.com",
  "phone": "9876543210",
  "message": "I am interested in discussing a new marketing project with your team.",
  "project_type": "Marketing",
  "services": ["SEO", "Content Writing"],
  "attachment": "file.pdf"
}
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "message": "Your message has been sent successfully!",
  "data": {
    "id": 1,
    "name": "Jane Smith",
    "email": "jane.smith@example.com",
    "phone": "9876543210",
    "message": "I am interested in discussing a new marketing project with your team.",
    "project_type": "Marketing",
    "services": ["SEO", "Content Writing"],
    "attachment_path": "contact-attachments/filename.pdf",
    "created_at": "2026-02-10T12:00:00",
    "updated_at": "2026-02-10T12:00:00"
  }
}
```

### 2. **Response Details:**

* **code:** The HTTP status code indicating the result of the request (e.g., `200 OK` for a successful submission).
* **status:** The status of the request (e.g., `OK`).
* **message:** A user-friendly message describing the outcome of the request.
* **data:** The contact message data that was stored, including the ID, name, email, phone, message, project type, services, and the file path if an attachment was included.

### 3. **Error Handling**

In case of validation errors or missing required fields, the API will return a 422 status with detailed error messages.

#### Example of Validation Error Response:

```json
{
  "code": 422,
  "status": "Unprocessable Entity",
  "message": "The name field is required."
}
```

### Response Codes:

* `200 OK`: The contact message was successfully submitted.
* `422 Unprocessable Entity`: The request was invalid (e.g., missing required fields).
* `500 Internal Server Error`: There was an unexpected server issue.

## Notes on File Upload

If the user includes an attachment, it will be stored in the `contact-attachments` directory. The API supports files with the following extensions:

* PDF
* JPG, JPEG, PNG
* DOC, DOCX

The file will be validated to ensure it meets the maximum size limit and is of an allowed type. If an invalid file is uploaded, an error will be returned.
