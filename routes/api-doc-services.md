# API Documentation: Services

## Introduction

This document provides a comprehensive guide for front-end developers to integrate with the Services API. The API enables the retrieval of service data, either as a list of services or by a specific service’s ID. It includes support for filtering by category.

### **Base URL:**

```
https://n-tawasull.sa/api
```

---
### **Base URL (Storage):**

```
https://n-tawasull.sa/storage
```

---

## API Endpoints

### 1. **Get All Services** (with optional filters)

* **Endpoint:** `GET /api/services`
* **Description:** This endpoint retrieves a list of all services. You can filter services by category using the `category_id` parameter.

#### Query Parameters:

* `category_id` (Optional): Filters services by category.

#### Example Request:

```
GET /api/services?category_id=1
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "data": [
    {
      "id": 1,
      "title": "خدمة تسويق",
      "description": "خدمة تسويق",
      "category_id": 1,
      "category": "تسويق",
      "tags": ["تسويق", "اعمال", "فوتوشوب"],
      "main_image": "services/O77bYpmsH5RP0mtWyhbW51EWdQDe3X9MZY2Apqq9.jpg",
      "images": [
        "services/gallery/436wcWsty1dxQcBo64NWbSmTnnRKdUK7VHF7s8SX.jpg",
        "services/gallery/JWZp7c6q7lcO0xOlqb27DySbrqv0Csoi2KvrwBl0.jpg",
        "services/gallery/ND8ojqBPzxWL3O4LCMMkq5HGInaphIFKx1ZERv3v.jpg"
      ],
      "display_order": 1
    }
  ]
}
```

### 2. **Get Specific Service by ID**

* **Endpoint:** `GET /api/services/{id}`
* **Description:** This endpoint retrieves the details of a specific service by its ID.

#### Example Request:

```
GET /api/services/1
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "data": {
    "id": 1,
    "title": "خدمة تسويق",
    "description": "خدمة تسويق",
    "category": "تسويق",
    "tags": ["تسويق", "اعمال", "فوتوشوب"],
    "main_image": "services/O77bYpmsH5RP0mtWyhbW51EWdQDe3X9MZY2Apqq9.jpg",
    "images": [
      "services/gallery/436wcWsty1dxQcBo64NWbSmTnnRKdUK7VHF7s8SX.jpg",
      "services/gallery/JWZp7c6q7lcO0xOlqb27DySbrqv0Csoi2KvrwBl0.jpg",
      "services/gallery/ND8ojqBPzxWL3O4LCMMkq5HGInaphIFKx1ZERv3v.jpg"
    ],
    "display_order": 1
  }
}
```

### 3. **Get Services by Category**

* **Endpoint:** `GET /api/services?category_id={category_id}`
* **Description:** Retrieves a list of services filtered by the specified category ID.

#### Query Parameters:

* `category_id` (Required): The ID of the category to filter services by.

#### Example Request:

```
GET /api/services?category_id=1
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "data": [
    {
      "id": 1,
      "title": "خدمة تسويق",
      "description": "خدمة تسويق",
      "category_id": 1,
      "category": "تسويق",
      "tags": ["تسويق", "اعمال", "فوتوشوب"],
      "main_image": "services/O77bYpmsH5RP0mtWyhbW51EWdQDe3X9MZY2Apqq9.jpg",
      "images": [
        "services/gallery/436wcWsty1dxQcBo64NWbSmTnnRKdUK7VHF7s8SX.jpg",
        "services/gallery/JWZp7c6q7lcO0xOlqb27DySbrqv0Csoi2KvrwBl0.jpg",
        "services/gallery/ND8ojqBPzxWL3O4LCMMkq5HGInaphIFKx1ZERv3v.jpg"
      ],
      "display_order": 1
    }
  ]
}
```

## Data Structure Overview

Each service returned by the API follows this structure:

* **id:** The unique identifier for the service.
* **title:** The title or name of the service.
* **description:** A brief description of the service.
* **category_id:** The category ID of the service (used for filtering).
* **category:** The name of the service's category.
* **tags:** An array of tags related to the service.
* **main_image:** The URL of the main image for the service.
* **images:** An array of additional images related to the service.
* **display_order:** The display order of the service in listings.

### Service Tags Example:

```json
{
  "tags": ["تسويق", "اعمال", "فوتوشوب"]
}
```

### Service Images Example:

```json
{
  "images": [
    "services/gallery/436wcWsty1dxQcBo64NWbSmTnnRKdUK7VHF7s8SX.jpg",
    "services/gallery/JWZp7c6q7lcO0xOlqb27DySbrqv0Csoi2KvrwBl0.jpg",
    "services/gallery/ND8ojqBPzxWL3O4LCMMkq5HGInaphIFKx1ZERv3v.jpg"
  ]
}
```

## Error Handling

### Common Error Response:

```json
{
  "code": 404,
  "status": "Not Found",
  "message": "Service not found or is inactive"
}
```

### Response Codes:

* `200 OK`: The request was successful.
* `404 Not Found`: The service does not exist or is inactive.
