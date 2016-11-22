# GET

# POST

## auth (token)

Validates a user token.

```
String token: The user's token.
```

Returns success or failure, along with extra information if the authentication failed.

## login (email, password)

Logs a user in.

```
String username: The user's username.
String password: The user's password.
optional boolean persistent: If the user should be remembered.
```

Returns success or failure, along with extra information if the login failed.

## logout ()

Logs out the current user.

Returns boolean based on success.

#### register (email, password, dci)

Register a new user.

```
String email:    The user's email.
String password: The user's password.
int    dci:      The user's dci number.
```