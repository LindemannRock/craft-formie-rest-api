# GraphQL Examples for Formie REST API Plugin

This document provides ready-to-use GraphQL queries for accessing Formie data through the Formie REST API plugin's GraphQL endpoints.

## Setting Up GraphQL Access

### 1. Create a GraphQL Token

1. Navigate to **Craft CP → GraphQL → Tokens**
2. Click **New Token**
3. Configure permissions:
   - **General**: 
     - ✅ View forms
     - ✅ View submissions
   - **Forms** (if section exists):
     - ✅ View forms
   - **Formie**:
     - ✅ View all forms
     - ✅ View all submissions
4. Save and copy the generated token

### 2. Test Your Token

```bash
curl -X POST https://ahf.ddev.site/api \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"query": "{ formieForms { handle title } }"}'
```

## Common Queries

### 1. List All Forms

```graphql
query ListAllForms {
  formieForms {
    id
    uid
    handle
    title
    dateCreated
    dateUpdated
  }
}
```

### 2. Get Form with All Details

```graphql
query GetFormDetails($handle: String!) {
  formieForm(handle: $handle) {
    id
    handle
    title
    settings {
      submitActionMessage
      submitActionMessageTimeout
      submitActionFormHide
      submitAction
      submitActionTab
      submitActionUrl
      submitActionRedirect
      submitMethod
      errorMessageHtml
    }
    pages {
      id
      name
      sortOrder
      fields {
        id
        uid
        handle
        name
        type
        required
        instructions
        errorMessage
        placeholder
        defaultValue
        columnWidth
        
        # Field-specific properties
        ... on Field_SingleLineText {
          limit
          limitType
          limitAmount
        }
        
        ... on Field_MultiLineText {
          limit
          limitType
          limitAmount
          rows
        }
        
        ... on Field_Email {
          uniqueValue
        }
        
        ... on Field_Number {
          min
          max
        }
        
        ... on Field_Dropdown {
          options {
            label
            value
            isDefault
          }
        }
        
        ... on Field_Radio {
          layout
          options {
            label
            value
            isDefault
          }
        }
        
        ... on Field_Checkboxes {
          layout
          options {
            label
            value
            isDefault
          }
        }
        
        ... on Field_Date {
          displayType
          dateFormat
          timeFormat
          includeTime
        }
        
        ... on Field_Phone {
          countryEnabled
          countryDefaultValue
        }
        
        ... on Field_Name {
          useMultipleName
          firstNameLabel
          lastNameLabel
          middleNameLabel
          prefixLabel
        }
        
        ... on Field_Address {
          address1Label
          address2Label
          address3Label
          cityLabel
          stateLabel
          zipLabel
          countryLabel
        }
      }
    }
  }
}
```

### 3. Get Recent Submissions

```graphql
query GetRecentSubmissions($limit: Int = 10) {
  formieSubmissions(limit: $limit, orderBy: "dateCreated DESC") {
    id
    title
    dateCreated
    status
    form {
      handle
      title
    }
  }
}
```

### 4. Get Submissions for Specific Form

```graphql
query GetFormSubmissions($formHandle: [String]!, $limit: Int = 20) {
  formieSubmissions(form: $formHandle, limit: $limit) {
    id
    title
    dateCreated
    dateUpdated
    status
    
    # Dynamic field access based on form
    ... on customerFeedback_Submission {
      customerName
      memberID
      memberEmail
      memberMobile
      feedbackMessage
      satisfactionRating
    }
  }
}
```

Variables:
```json
{
  "formHandle": ["customerFeedback"],
  "limit": 20
}
```

### 5. Get Submissions with Date Range

```graphql
query GetSubmissionsByDateRange(
  $formHandle: [String]!
  $startDate: String!
  $endDate: String!
) {
  formieSubmissions(
    form: $formHandle
    dateCreated: ["and", ">= " + $startDate, "<= " + $endDate]
  ) {
    id
    dateCreated
    form {
      handle
    }
    # Add your form-specific fields here
  }
}
```

Variables:
```json
{
  "formHandle": ["customerFeedback"],
  "startDate": "2025-01-01 00:00:00",
  "endDate": "2025-01-31 23:59:59"
}
```

### 6. Get Submission Count

```graphql
query GetSubmissionStats($formHandle: [String]) {
  submissions: formieSubmissions(form: $formHandle) {
    totalCount
  }
  
  recentSubmissions: formieSubmissions(
    form: $formHandle
    dateCreated: ">= 7 days ago"
  ) {
    totalCount
  }
}
```

### 7. Get Complete Submission Data

```graphql
query GetSubmissionDetails($id: [QueryArgument]!) {
  formieSubmission(id: $id) {
    id
    title
    dateCreated
    dateUpdated
    status
    form {
      handle
      title
    }
    
    # Method 1: Access all field values as JSON
    fieldValues
    
    # Method 2: Access specific fields (replace with your form handle)
    ... on customerFeedback_Submission {
      customerName
      memberID
      memberEmail
      memberMobile
      feedbackMessage
      # Add all your form fields here
    }
  }
}
```

### 8. Search Submissions

```graphql
query SearchSubmissions($search: String!, $formHandle: [String]) {
  formieSubmissions(
    search: $search
    form: $formHandle
    limit: 50
  ) {
    id
    title
    dateCreated
    form {
      handle
    }
    # Highlight matched content
    searchScore
  }
}
```

## Advanced Queries

### 9. Get Forms with Submission Count

```graphql
query GetFormsWithStats {
  formieForms {
    id
    handle
    title
    
    # Get submission count for each form
    submissions {
      totalCount
    }
    
    # Get recent submissions count
    recentSubmissions: submissions(dateCreated: ">= 7 days ago") {
      totalCount
    }
  }
}
```

### 10. Paginated Submissions

```graphql
query GetPaginatedSubmissions(
  $formHandle: [String]!
  $limit: Int = 20
  $offset: Int = 0
) {
  formieSubmissions(
    form: $formHandle
    limit: $limit
    offset: $offset
    orderBy: "dateCreated DESC"
  ) {
    id
    title
    dateCreated
    
    # Include pagination info
    ... on customerFeedback_Submission {
      customerName
      memberEmail
    }
  }
  
  # Get total count for pagination
  total: formieSubmissions(form: $formHandle) {
    totalCount
  }
}
```

### 11. Export-Ready Query

Get all submission data in a format ready for export:

```graphql
query ExportSubmissions($formHandle: [String]!, $startDate: String) {
  formieSubmissions(
    form: $formHandle
    dateCreated: ">= " + $startDate
    limit: 1000
  ) {
    id
    dateCreated
    
    # Get all field values as key-value pairs
    fieldValues
    
    # Or get specific fields for CSV export
    ... on customerFeedback_Submission {
      customerName
      memberID
      memberEmail
      memberMobile
      feedbackType
      feedbackMessage
      satisfactionRating
      wouldRecommend
    }
  }
}
```

## Using with Different Tools

### GraphiQL (Built into Craft)

1. Navigate to: `https://ahf.ddev.site/admin/graphiql`
2. Add your token in Headers:
   ```json
   {
     "Authorization": "Bearer YOUR_TOKEN_HERE"
   }
   ```
3. Paste any query above and run

### Postman

1. Create new request
2. Set method to POST
3. URL: `https://ahf.ddev.site/api`
4. Headers:
   - `Authorization: Bearer YOUR_TOKEN_HERE`
   - `Content-Type: application/json`
5. Body (raw JSON):
   ```json
   {
     "query": "{ formieForms { handle title } }",
     "variables": {}
   }
   ```

### JavaScript/Fetch

```javascript
const query = `
  query GetForms {
    formieForms {
      handle
      title
    }
  }
`;

fetch('https://ahf.ddev.site/api', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer YOUR_TOKEN_HERE'
  },
  body: JSON.stringify({ query })
})
.then(res => res.json())
.then(data => console.log(data));
```

### PHP/Guzzle

```php
$client = new \GuzzleHttp\Client();

$query = '
  query GetForms {
    formieForms {
      handle
      title
    }
  }
';

$response = $client->post('https://ahf.ddev.site/api', [
    'headers' => [
        'Authorization' => 'Bearer YOUR_TOKEN_HERE',
        'Content-Type' => 'application/json',
    ],
    'json' => [
        'query' => $query,
    ],
]);

$data = json_decode($response->getBody(), true);
```

## Tips

1. **Use Fragments** for repeated field selections:
   ```graphql
   fragment SubmissionFields on customerFeedback_Submission {
     customerName
     memberID
     memberEmail
   }
   
   query {
     formieSubmissions {
       ...SubmissionFields
     }
   }
   ```

2. **Alias Fields** when you need multiple queries:
   ```graphql
   query {
     allForms: formieForms {
       handle
     }
     activeForms: formieForms(status: "enabled") {
       handle
     }
   }
   ```

3. **Use Variables** for dynamic queries instead of string concatenation

4. **Request Only What You Need** - GraphQL allows precise field selection

5. **Check the Schema** in GraphiQL for all available fields and filters