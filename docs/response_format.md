Response Format
====================

The API will send a uniform response format:

```
[status]    1 or 0  : Success or Failure.
[code]      INT     : HTTP Status Code.
[message]   STRING  : Detailed message about result.
[objects]   ARRAY {
    [###]       MIXED   : An object from the result.
}
```