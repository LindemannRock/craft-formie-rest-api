# Security Guide for Formie API

## Overview

This guide outlines the security measures implemented in the Formie API module and best practices for secure deployment.

## Security Layers

### 1. API Key Authentication
- **Environment-specific keys**: Different keys for dev/staging/production
- **Permission-based access**: Keys have specific permissions (read_forms, read_submissions, etc.)
- **Secure generation**: 256-bit random keys with proper prefixes

### 2. Rate Limiting
- **Per-key limits**: Each API key has individual rate limits
- **Environment-based**: Production has different limits than staging
- **Time window**: 1-hour sliding window
- **Headers**: Rate limit info in response headers

### 3. IP Whitelisting (Optional)
- **Environment-specific**: Different IP restrictions per environment
- **CIDR support**: Subnet-based IP filtering
- **Configurable**: Can be disabled for development

### 4. Request Signing (Production Only)
- **HMAC-SHA256**: Cryptographic signature validation
- **Timestamp validation**: Prevents replay attacks (5-minute window)
- **Automatic**: Only required in production environment

### 5. CORS Protection
- **Origin validation**: Environment-specific allowed origins
- **Method restrictions**: Only GET, POST, OPTIONS allowed
- **Header controls**: Specific headers allowed

### 6. Input Validation & Sanitization
- **Parameter validation**: All inputs validated
- **SQL injection protection**: Parameterized queries
- **XSS prevention**: Output sanitization
- **Field filtering**: Only allowed fields returned

### 7. Logging & Monitoring
- **Access logs**: All API requests logged
- **Security events**: Failed auth attempts tracked
- **Partial key logging**: Only first 10 characters logged
- **Environment context**: Logs include environment info

## Implementation Details

### API Key Security

```php
// Keys stored in environment variables
FORMIE_API_KEY=sk_live_[64-character-hex]
FORMIE_API_KEY_LIMITED=sk_limited_[64-character-hex]
FORMIE_API_KEY_TEST=sk_test_[64-character-hex]  // Dev only
```

### Rate Limiting

| Environment | Primary Key | Limited Key |
|-------------|-------------|-------------|
| Production  | 1000/hour   | 100/hour    |
| Staging     | 500/hour    | 50/hour     |
| Development | 1000/hour   | 1000/hour   |

### Request Signing (Production)

Required headers for production:
```
X-API-Key: sk_live_your-key
X-Timestamp: 1642694400
X-Signature: sha256-hash-of-request
```

Signature calculation:
```
signature = HMAC-SHA256(method + "\n" + path + "\n" + timestamp + "\n" + body, secret)
```

### IP Whitelisting

Configure in `ApiKeyService.php`:
```php
private function getIpWhitelistForEnvironment(): array
{
    return match(Craft::$app->env) {
        'production' => ['192.168.1.0/24', '10.0.0.0/8'],
        'staging' => ['192.168.1.0/24'],
        default => [], // No restrictions in dev
    };
}
```

## Security Best Practices

### For Administrators

1. **Key Management**
   - Use different keys for each environment
   - Rotate keys every 90 days
   - Store keys in secure password manager
   - Never commit keys to version control

2. **Network Security**
   - Always use HTTPS in production
   - Configure proper firewall rules
   - Use VPN for admin access
   - Enable IP whitelisting if possible

3. **Monitoring**
   - Set up log monitoring and alerts
   - Monitor rate limit violations
   - Track failed authentication attempts
   - Regular security audits

### For Developers

1. **Environment Configuration**
   ```bash
   # Generate secure keys
   openssl rand -hex 32
   
   # Set environment variables
   export FORMIE_API_KEY="sk_live_$(openssl rand -hex 32)"
   export FORMIE_API_KEY_LIMITED="sk_limited_$(openssl rand -hex 32)"
   ```

2. **Request Implementation**
   ```php
   // Example secure request
   $signature = hash_hmac('sha256', 
       $method . "\n" . $path . "\n" . $timestamp . "\n" . $body, 
       $secret
   );
   
   $headers = [
       'X-API-Key' => $apiKey,
       'X-Timestamp' => $timestamp,
       'X-Signature' => $signature,
   ];
   ```

3. **Error Handling**
   ```php
   // Don't expose sensitive information
   try {
       // API call
   } catch (Exception $e) {
       // Log full error internally
       error_log($e->getMessage());
       
       // Return generic error to client
       return ['error' => 'Request failed'];
   }
   ```

## Production Checklist

### Before Deployment
- [ ] Generate unique API keys for production
- [ ] Set up proper environment variables
- [ ] Configure IP whitelisting if needed
- [ ] Test rate limiting functionality
- [ ] Set up monitoring and alerting
- [ ] Configure CORS for production domains
- [ ] Enable request signing validation

### Security Testing
- [ ] Test authentication with invalid keys
- [ ] Verify rate limiting works correctly
- [ ] Test CORS configuration
- [ ] Validate request signing
- [ ] Test IP whitelisting
- [ ] Verify proper error responses
- [ ] Check log output for sensitive data

### Monitoring Setup
- [ ] Set up API access logging
- [ ] Configure rate limit alerts
- [ ] Monitor failed authentication attempts
- [ ] Set up security event notifications
- [ ] Configure log rotation
- [ ] Set up log analysis tools

## Common Security Threats & Mitigations

### 1. Brute Force Attacks
- **Threat**: Automated attempts to guess API keys
- **Mitigation**: Rate limiting, account lockout, monitoring

### 2. Replay Attacks
- **Threat**: Reusing captured requests
- **Mitigation**: Timestamp validation, nonce implementation

### 3. Man-in-the-Middle
- **Threat**: Intercepting API communications
- **Mitigation**: HTTPS enforcement, certificate pinning

### 4. Data Exposure
- **Threat**: Sensitive data in responses
- **Mitigation**: Field filtering, output sanitization

### 5. Injection Attacks
- **Threat**: SQL injection, XSS
- **Mitigation**: Input validation, parameterized queries

## Incident Response

### If API Key is Compromised
1. Immediately revoke the compromised key
2. Generate new key with different value
3. Update all systems using the key
4. Review access logs for suspicious activity
5. Notify relevant stakeholders

### If Security Breach is Detected
1. Isolate affected systems
2. Preserve evidence and logs
3. Assess scope of breach
4. Implement containment measures
5. Notify appropriate authorities
6. Update security measures

## Advanced Security Features

### Future Enhancements
- **OAuth 2.0**: Token-based authentication
- **JWT tokens**: Stateless authentication
- **API versioning**: Deprecation management
- **Audit trails**: Comprehensive logging
- **Threat detection**: ML-based anomaly detection

### Integration Options
- **WAF**: Web Application Firewall
- **CDN**: Content Delivery Network protection
- **SIEM**: Security Information and Event Management
- **Secrets management**: HashiCorp Vault, AWS Secrets Manager

## Support

For security issues:
- **Internal**: Contact the development team
- **External**: Report to security@lindemannrock.com
- **Urgent**: Follow incident response procedures

Remember: Security is an ongoing process, not a one-time setup. Regular reviews and updates are essential.