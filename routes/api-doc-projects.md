# Front-End Developer API Documentation: Projects

## Introduction

This documentation provides detailed information on how to interact with the Projects API, which allows front-end developers to retrieve project data, either in full or by category. The API allows two primary endpoints: one for retrieving all projects and another for fetching a specific project by its ID.

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

### 1. **Get All Projects** (with optional filters)

* **Endpoint:** `GET /api/projects`
* **Description:** This endpoint retrieves a list of all projects. You can apply filters such as `category_id` to filter projects by their category.

#### Parameters:

* `category_id` (Optional): Filters projects by the specified category ID.

#### Example Request:

```
GET /api/projects?category_id=1
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "data": [
    {
      "id": 2,
      "title": "مشروع تسويق",
      "description": "مشروع تسويق",
      "category_id": 1,
      "category": "تسويق",
      "main_image": "projects/3Jh0P86LrCFWpggTbc4g0OtNOCIBe9tFSFu7V5y2.jpg",
      "display_order": 2
    }
  ]
}
```

### 2. **Get Specific Project by ID**

* **Endpoint:** `GET /api/projects/{id}`
* **Description:** This endpoint retrieves the details of a specific project by its ID.

#### Example Request:

```
GET /api/projects/1
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "data": {
    "id": 1,
    "title": "مشروع برمجة",
    "description": "بمشروع رمجة",
    "category": "برمجة",
    "main_image": "projects/XGdbIh0W5UvWnwuoKGYiEAEw9e6jlujLONSL66E1.jpg",
    "images": [
      "projects/gallery/ODn98jFh09QmdUsOlRWCHiTySojY0loID4AQ7IVq.jpg",
      "projects/gallery/I4lEWHsgAvesDW85DyrpFGD8dG2fxA8ezairddcL.jpg"
    ],
    "features": [
      {
        "title": "ويب حديث",
        "description": "Laravel"
      },
      {
        "title": "PWA",
        "description": "PWA app"
      }
    ],
    "content": "<h1>Hellow World</h1>",
    "videos": [
      {
        "type": "url",
        "provider": "youtube",
        "title": null,
        "url": "https://www.youtube.com/watch?v=y2qX4NRXgAE",
        "iframe": null
      },
      {
        "type": "iframe",
        "provider": "other",
        "title": "برمجة",
        "url": null,
        "iframe": "<iframe width=\"560\" height=\"315\" src=\"https://www.youtube.com/embed/y2qX4NRXgAE?si=l56OR2xKQdL24YrY\" title=\"YouTube video player\" frameborder=\"0\" allow=\"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share\" referrerpolicy=\"strict-origin-when-cross-origin\" allowfullscreen></iframe>"
      }
    ],
    "display_order": 1
  }
}
```

### 3. **Get Projects by Category**

* **Endpoint:** `GET /api/projects?category_id={category_id}`
* **Description:** Retrieves a list of projects filtered by the given category ID.

#### Parameters:

* `category_id` (Required): The ID of the category to filter projects.

#### Example Request:

```
GET /api/projects?category_id=1
```

#### Example Response:

```json
{
  "code": 200,
  "status": "OK",
  "data": [
    {
      "id": 2,
      "title": "مشروع تسويق",
      "description": "مشروع تسويق",
      "category_id": 1,
      "category": "تسويق",
      "main_image": "projects/3Jh0P86LrCFWpggTbc4g0OtNOCIBe9tFSFu7V5y2.jpg",
      "display_order": 2
    }
  ]
}
```

## Data Structure Overview

The response data for each project follows this structure:

* **id:** The unique identifier of the project.
* **title:** The title of the project.
* **description:** A brief description of the project.
* **category_id:** The ID of the project's category.
* **category:** The name of the project category.
* **main_image:** The URL of the project's main image.
* **display_order:** The order in which the project appears.

### Project Detail Data:

For a single project detail, the response includes:

* **images:** A list of images related to the project.
* **features:** A list of features associated with the project.
* **content:** Additional content or description for the project (HTML Code).
* **videos:** A list of video URLs or embedded video iframes related to the project.

### Example of Project Feature:

```json
{
  "title": "ويب حديث",
  "description": "Laravel"
}
```

### Example of Project Video:

```json
{
  "type": "url",
  "provider": "youtube",
  "title": null,
  "url": "https://www.youtube.com/watch?v=y2qX4NRXgAE",
  "iframe": null
}
```

## Error Handling

### Common Error Response:

```json
{
  "code": 404,
  "status": "Not Found",
  "message": "Project not found or is inactive"
}
```

### Response Codes:

* `200 OK`: The request was successful.
* `404 Not Found`: The project does not exist or is inactive.