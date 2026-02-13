# API Documentation: Analytics Tracking

## Introduction

This API provides front-end developers with a mechanism to track user interactions on the site. By sending event data to the server, you can track user behaviors and collect insights for analysis. The tracking system records events such as clicks, page views, and interactions with specific entities like buttons or links.

### **Base URL:**

```
https://n-tawasull.sa/api
```

---

  ## API Endpoint

  ### 1. **Track User Events**

  * **Endpoint:** `POST /api/analytics/track`
  * **Description:** This endpoint allows for tracking of various user interactions on the website. It accepts an event name and additional data related to the event.

  #### Request Body:

  The payload should contain the following fields:

  * **event** (required): The name of the event being tracked (e.g., "button_click", "page_view").
  * **entity_type** (optional): The type of entity being interacted with (e.g., "button", "form", "link").
  * **entity_id** (optional): A unique identifier for the entity (e.g., the ID of a product or article).
  * **source** (optional): The source of the event (e.g., "homepage", "footer", "sidebar").
  * **page** (optional): The current page URL (defaults to the current page if not provided).

  #### Example Request:

  ```json
  {
    "event": "button_click",
    "entity_type": "button",
    "entity_id": "12345",
    "source": "homepage",
    "page": "/home"
  }
  ```

  #### Example Response:

  ```json
  {
    "code": 204,
    "status": "No Content"
  }
  ```

  * **code:** The HTTP status code indicating the result of the request (`204 No Content` indicates a successful request with no response body).
  * **status:** The status of the request (e.g., `No Content`).

  ### Event Tracking on the Front-End

  Front-end developers can track events using the following approach:

  * Add the `data-analytics` attribute to the elements you want to track (e.g., buttons, links).
  * Specify event-related data as data attributes:

    * `data-event`: The name of the event to track (e.g., "button_click").
    * `data-entity`: The entity type (e.g., "button", "form").
    * `data-id`: A unique identifier for the entity.
    * `data-source`: The source of the interaction (e.g., "homepage").

  #### Example HTML for an Element:

  ```html
  <button data-analytics
          data-event="button_click"
          data-entity="button"
          data-id="12345"
          data-source="homepage">
      Click Me
  </button>
  ```

  ### JavaScript Integration

  The JavaScript snippet below listens for clicks on elements with the `data-analytics` attribute. When an event is triggered, it sends the event data to the server using either the `navigator.sendBeacon` method (preferred) or the `fetch` API (as a fallback).

  #### Example JavaScript:

  ```javascript
  document.addEventListener("click", function (e) {
      const el = e.target.closest("[data-analytics]");
      if (!el) return;

      const payload = {
          event: el.dataset.event,
          entity_type: el.dataset.entity,
          entity_id: el.dataset.id,
          source: el.dataset.source,
          page: window.location.pathname,
      };

      if (navigator.sendBeacon) {
          const blob = new Blob([JSON.stringify(payload)], {
              type: "application/json",
          });
          navigator.sendBeacon("/analytics/track", blob);
      } else {
          // fallback
          fetch("/analytics/track", {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": document
                      .querySelector('meta[name="csrf-token"]')
                      ?.getAttribute("content"),
              },
              body: JSON.stringify(payload),
              keepalive: true,
          });
      }
  });
  ```

  ### Key Features:

  * **Tracking Clicks**: Any clickable element with `data-analytics` will trigger event tracking when clicked.
  * **Event Data**: The data attributes (`data-event`, `data-entity`, `data-id`, `data-source`) provide rich context for analytics tracking.
  * **Fallback**: If the browser does not support `sendBeacon`, the event is sent using the `fetch` API.

  ### Analytics Event Data Structure:

  When tracking events, the server stores the following data:

  * **event**: The name of the event (e.g., "button_click").
  * **entity_type**: The type of the entity interacted with (e.g., "button").
  * **entity_id**: A unique identifier for the entity (e.g., the ID of the button).
  * **page**: The URL of the page where the event occurred.
  * **source**: The source or context of the event (e.g., "homepage").
  * **ip**: The IP address of the user triggering the event.
  * **user_agent**: The user agent string of the user's browser/device.
  * **user_id**: The authenticated user ID (if available).

  ## Error Handling

  If an error occurs during the tracking process (e.g., invalid data), the server will return appropriate error messages. Common status codes include:

  * **400 Bad Request**: If the required data is missing or invalid.
  * **500 Internal Server Error**: If there is an issue with the server processing the event.

  ### Example of Error Response:

  ```json
  {
    "code": 400,
    "status": "Bad Request",
    "message": "Invalid event data"
  }
  ```