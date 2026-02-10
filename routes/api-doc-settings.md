## API Documentation for Settings

### **Overview**

This API allows you to fetch settings in a structured format. It supports retrieving all settings or specific settings by their key.

---

### **Base URL:**

```
https://n-tawasull.sa/api
```

---

### **Available Routes**

#### 1. **Get All Settings**

* **Route**: `/settings`
* **Method**: `GET`
* **Description**: Retrieve all settings or filter them by group.
* **Query Parameters**:

  * `group` (optional): Filter settings by group name.
* **Response**:

  * **200 OK**: Returns a list of all settings or filtered settings.
  * **404 Not Found**: If no settings are found.

**Example Request:**

```http
GET https://n-tawasull.sa/api/settings?group=general
```

**Example Response (200 OK):**

```json
{
  "code": 200,
  "status": "OK",
  "data": [
    {
      "key": "site_name",
      "value": "نقطة تواصل",
      "type": "string",
      "group": "general"
    },
    {
      "key": "site_description",
      "value": "وكالة تسويق إبداعية",
      "type": "text",
      "group": "general"
    }
    // More settings...
  ]
}
```

---

#### 2. **Get Specific Setting by Key**

* **Route**: `/settings/{key}`
* **Method**: `GET`
* **Description**: Retrieve a specific setting by its key.
* **Path Parameters**:

  * `key`: The key of the setting to retrieve.
* **Response**:

  * **200 OK**: Returns the setting with the specified key.
  * **404 Not Found**: If the setting with the provided key does not exist.

**Example Request:**

```http
GET https://n-tawasull.sa/api/settings/faqs.items
```

**Example Response (200 OK):**

```json
{
  "code": 200,
  "status": "OK",
  "data": {
    "key": "faqs.items",
    "value": "[{\"question\":\"كيف يعمل نموذج الاشتراك؟\",\"answer\":\"أنت تدفع رسماً شهرياً ثابتاً وتحصل على إمكانية الوصول إلى فريق تصميم مخصص. يمكنك تقديم طلبات غير محدودة، وسنقوم بتسليمها واحداً تلو الآخر (أو اثنين في وقت واحد مع الباقة الاحترافية). لا توجد فواتير بالساعة، ولا عقود — يمكنك الإلغاء أو الإيقاف مؤقتاً في أي وقت.\"},{\"question\":\"ما هي أنواع مهام التصميم التي يمكنني طلبها؟\",\"answer\":\"كل شيء بدءاً من تصميم الشعارات والهوية البصرية إلى واجهات المستخدم (UI/UX) لمواقع الويب وتطبيقات الجوال، ورسومات منصات التواصل الاجتماعي، وتطوير المواقع الكاملة باستخدام Framer.\"},{\"question\":\"ما هي سرعة استلام التصاميم؟\",\"answer\":\"يتم إكمال معظم الطلبات في غضون 48 ساعة أو أقل. قد تستغرق المشاريع المعقدة مثل مواقع الويب متعددة الصفحات وقتاً أطول قليلاً.\"},{\"question\":\"ما هي الأدوات التي تستخدمونها لإدارة العمل؟\",\"answer\":\"نستخدم بشكل أساسي Slack للتواصل و Figma للتصميم. نوفر أيضاً بوابة عملاء مخصصة لتتبع المشاريع والمهام بسلاسة.\"},{\"question\":\"هل هناك حد لعدد الطلبات التي يمكنني تقديمها؟\",\"answer\":\"لا! يمكنك إضافة أي عدد تريده من الطلبات إلى قائمة الانتظار الخاصة بك، وسنعمل عليها واحداً تلو الآخر بناءً على أولوياتك.\"},{\"question\":\"هل يمكنني الإلغاء أو الإيقاف المؤقت في أي وقت؟\",\"answer\":\"بالتأكيد. لا توجد التزامات طويلة الأمد. يمكنك إيقاف اشتراكك أو إلغاؤه في أي وقت بنقرة واحدة فقط.\"}]",
    "type": "json",
    "group": "faqs"
  }
}
```

---

### **API Response Format**

* The response is a JSON object containing the following keys:

  * **code**: HTTP status code (e.g., 200 for success).
  * **status**: The status of the response (e.g., "OK").
  * **data**: The array or object containing the settings information.

---

### **Setting Schema**

Each setting returned in the API follows this schema:

```json
{
  "key": "string",      // The unique key for the setting
  "value": "string",    // The value of the setting
  "type": "string",     // The type of the setting (e.g., "string", "json", "color", "image")
  "group": "string",    // The group to which the setting belongs (e.g., "general", "branding")
}
```

**Setting Types**:

* **string**: A simple string value.
* **text**: A longer text value.
* **json**: A JSON object or array stored as a string.
* **color**: A color value in hex format.
* **image**: A URL to an image.

---

### **Error Responses**

* **404 Not Found**: When a setting is not found or when the route doesn't exist.
* **500 Internal Server Error**: When something goes wrong on the server side.

---

### **Example Usage**

For retrieving all settings in the "general" group:

```http
GET https://n-tawasull.sa/api/settings?group=general
```

For retrieving a specific setting, such as "contact.whatsapp":

```http
GET https://n-tawasull.sa/api/settings/contact.whatsapp
```

---

This API allows the front-end to easily integrate and fetch settings dynamically, providing structured responses for easy parsing. Let me know if you need any further assistance!
