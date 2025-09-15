# Formie REST API Test Guide

This guide demonstrates the available REST API endpoints provided by the Formie REST API plugin for testing what data external systems (like SAP) would receive when accessing Formie forms and submissions.

## Base URLs

- **Test Endpoints**: `/api/test/formie/*`
- **Production Endpoints**: `/api/v1/formie/*`

## Authentication

All API endpoints require authentication using an API key in the request headers.

### Test API Keys

For testing purposes, use one of these API keys:

1. **Full Access Key**: `test_key_sap_integration_2025`
   - Permissions: Read forms, Read submissions
   - Rate limit: 1000 requests/hour

2. **Limited Access Key**: `test_key_limited_access_2025`
   - Permissions: Read forms only
   - Rate limit: 100 requests/hour

### How to Use API Keys

Include the API key in your request headers:
```
X-API-Key: test_key_sap_integration_2025
```

## Test Endpoints

### 1. Test Authentication
**URL:** `https://ahf.ddev.site/api/test/formie/auth`  
**Method:** GET  
**Headers:** 
- `X-API-Key: test_key_sap_integration_2025`

**Sample Response:**
```json
{
  "success": true,
  "data": {
    "authenticated": true,
    "apiKeyInfo": {
      "name": "SAP Test Integration",
      "permissions": ["read_forms", "read_submissions"],
      "rateLimit": 1000
    }
  },
  "meta": {
    "timestamp": "2025-01-16T10:00:00+00:00",
    "version": "1.0",
    "endpoint": "auth"
  }
}
```

### 2. List All Forms or Get Specific Form
**URL:** `https://ahf.ddev.site/api/test/formie/forms`  
**Method:** GET  
**Headers:** 
- `Accept: application/json` (optional)
- `X-API-Key: test_key_sap_integration_2025`

**Query Parameters (all optional):**
- `handle`: Get specific form by handle (e.g., `customerFeedback`)
- `id`: Get specific form by ID (e.g., `123`)

**Examples:**
- Get all forms: `/api/test/formie/forms`
- Get specific form by handle: `/api/test/formie/forms?handle=customerFeedback`
- Get specific form by ID: `/api/test/formie/forms?id=123`

**Sample Response:**
```json
{
  "success": true,
  "data": {
    "forms": [
      {
        "id": 123,
        "uid": "abc-123-def",
        "handle": "customerFeedback",
        "title": "Customer Feedback",
        "dateCreated": "2025-01-16T10:00:00+00:00",
        "dateUpdated": "2025-01-16T10:00:00+00:00",
        "submissionCount": 245,
        "fields": [
          {
            "handle": "memberID",
            "label": "Member ID",
            "type": "SingleLineText",
            "required": false,
            "instructions": ""
          },
          {
            "handle": "memberEmail",
            "label": "Member Email",
            "type": "Email",
            "required": true,
            "instructions": "Please enter your email"
          }
        ]
      }
    ],
    "totalForms": 1
  },
  "meta": {
    "timestamp": "2025-01-16T10:00:00+00:00",
    "version": "1.0",
    "endpoint": "forms"
  }
}
```

### 3. Get Form Submissions
**URL:** `https://ahf.ddev.site/api/test/formie/submissions`  
**Method:** GET  
**Headers:** 
- `Accept: application/json` (optional)
- `X-API-Key: test_key_sap_integration_2025`

**Query Parameters:**
- `formHandle` OR `formId` (one required): Form handle or ID
- `limit` (optional): Number of results per page (default: 10)
- `page` (optional): Page number (default: 1)
- `dateFrom` (optional): Filter submissions from this date (YYYY-MM-DD)
- `dateTo` (optional): Filter submissions until this date (YYYY-MM-DD)
- `status` (optional): Filter by status (enabled, disabled)

**Examples:**
- Get by handle: `/api/test/formie/submissions?formHandle=customerFeedback`
- Get by ID with pagination: `/api/test/formie/submissions?formId=123&limit=20&page=2`
- Get submissions from date range: `/api/test/formie/submissions?formHandle=customerFeedback&dateFrom=2025-01-01&dateTo=2025-01-31`

**Sample Response:**
```json
{
  "success": true,
  "data": {
    "form": {
      "id": 123,
      "handle": "customerFeedback",
      "title": "Customer Feedback"
    },
    "submissions": [
      {
        "id": 456,
        "uid": "xyz-456-abc",
        "title": "Submission #456",
        "dateCreated": "2025-01-16T09:30:00+00:00",
        "dateUpdated": "2025-01-16T09:30:00+00:00",
        "status": "enabled",
        "fields": {
          "memberID": {
            "label": "Member ID",
            "handle": "memberID",
            "type": "SingleLineText",
            "value": "MjMxMjM0NTc0ODQ0ODQ=",
            "required": false
          },
          "memberEmail": {
            "label": "Member Email",
            "handle": "memberEmail",
            "type": "Email",
            "value": "bXVoYW1tYWQuc2FtYWhhQGFsaGF0YWIuY29tLnNh",
            "required": true
          },
          "customerName": {
            "label": "Customer Name",
            "handle": "customerName",
            "type": "Name",
            "value": {
              "firstName": "Muhammad",
              "lastName": "Samaha",
              "fullName": "Muhammad Samaha"
            },
            "required": true
          },
          "rating": {
            "label": "Rating",
            "handle": "rating",
            "type": "Dropdown",
            "value": "5",
            "required": false
          }
        }
      }
    ],
    "pagination": {
      "total": 245,
      "perPage": 10,
      "currentPage": 1,
      "totalPages": 25,
      "hasMore": true
    }
  },
  "meta": {
    "timestamp": "2025-01-16T10:00:00+00:00",
    "version": "1.0",
    "endpoint": "submissions"
  }
}
```

## Production Endpoints

The production endpoints follow the same structure but with different URLs:

### Get All Forms
**URL:** `https://ahf.ddev.site/api/v1/formie/forms`  
**Method:** GET  
**Query Parameters:**
- `limit` (optional): Number of results (default: 100)
- `offset` (optional): Skip results (default: 0)
- `status` (optional): Filter by status (default: enabled)

### Get Form by ID
**URL:** `https://ahf.ddev.site/api/v1/formie/forms/{id}`  
**Method:** GET

### Get Form by Handle
**URL:** `https://ahf.ddev.site/api/v1/formie/forms/{handle}`  
**Method:** GET

### Get Submissions
**URL:** `https://ahf.ddev.site/api/v1/formie/submissions`  
**Method:** GET  
**Query Parameters:**
- `formId` or `formHandle` (one required)
- `limit` (optional): Number of results (default: 100)
- `offset` (optional): Skip results (default: 0)
- `dateFrom` (optional): Filter from date
- `dateTo` (optional): Filter to date
- `status` (optional): Filter by status (default: live)

### Get Submission by ID
**URL:** `https://ahf.ddev.site/api/v1/formie/submissions/{id}`  
**Method:** GET

## Testing with cURL

```bash
# Test authentication
curl -H "X-API-Key: test_key_sap_integration_2025" \
     https://ahf.ddev.site/api/test/formie/auth

# Get all forms
curl -H "Accept: application/json" \
     -H "X-API-Key: test_key_sap_integration_2025" \
     https://ahf.ddev.site/api/test/formie/forms

# Get specific form
curl -H "Accept: application/json" \
     -H "X-API-Key: test_key_sap_integration_2025" \
     "https://ahf.ddev.site/api/test/formie/forms?handle=customerFeedback"

# Get submissions for a specific form
curl -H "Accept: application/json" \
     -H "X-API-Key: test_key_sap_integration_2025" \
     "https://ahf.ddev.site/api/test/formie/submissions?formHandle=customerFeedback&limit=5"

# Test without API key (will fail)
curl -H "Accept: application/json" \
     https://ahf.ddev.site/api/test/formie/forms

# Test with limited access key (submissions will fail)
curl -H "Accept: application/json" \
     -H "X-API-Key: test_key_limited_access_2025" \
     "https://ahf.ddev.site/api/test/formie/submissions?formHandle=customerFeedback"
```

## Field Types

The API handles various Formie field types:

- **SingleLineText** - Returns string value
- **MultiLineText** - Returns string value
- **Number** - Returns numeric value
- **Email** - Returns email string
- **Phone** - Returns phone number string
- **Dropdown/Radio** - Returns selected value
- **Checkboxes** - Returns array of selected values
- **Date** - Returns ISO 8601 formatted date
- **Name** - Returns object with firstName, lastName, fullName
- **FileUpload** - Returns array of asset URLs with filenames

## Error Responses

### Missing API Key
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Invalid or missing API key. Please provide X-API-Key header."
  }
}
```

### Invalid Permissions
```json
{
  "success": false,
  "error": {
    "code": "FORBIDDEN",
    "message": "API key does not have permission to read submissions."
  }
}
```

### Form Not Found
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Form with handle 'invalidHandle' not found"
  }
}
```

### Missing Required Parameter
```json
{
  "success": false,
  "error": {
    "code": "MISSING_PARAMETER",
    "message": "Either formHandle or formId parameter is required"
  }
}
```

## Notes for Production Implementation

1. **Authentication**: Production will use environment-based API keys or database-stored keys
2. **Rate Limiting**: API calls will be rate-limited based on API key permissions
3. **Field Filtering**: Sensitive fields can be excluded from responses
4. **Data Format**: Base64 encoded values (memberID, memberEmail) are preserved as stored
5. **Pagination**: Large result sets are paginated for performance
6. **Caching**: Responses may be cached for improved performance
7. **HTTPS**: Always use HTTPS in production
8. **Monitoring**: API usage is logged for security and debugging

## Next Steps

1. Test these endpoints to verify the data structure meets your needs
2. Provide feedback on any additional fields or filters required
3. Define specific field requirements for SAP integration
4. Set up production API keys with appropriate permissions
5. Configure rate limiting and security measures