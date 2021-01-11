# Auth
## A basic PHP Authentication Package
###
The `Auth` constructor receives as a unique argument a `PDO` instance connected to your app's database.  
This connection is necessary to check credentials and register users.  
The login and logout method just puts or remove the user in the `user` session's key. In order to do so `Auth` makes use of the `marcosdipaolo/session` package.

### Usage
#### Configuration
```php
/** @var PDO $pdoInstance */
$auth = new MDP\Auth\Auth($pdoInstance);

// Setting the users table name
// The table the Auth package is going to interact with: 
$auth->setUsersTableName('members'); // default: 'users'

// Setting the table fields the package should manage
$auth->setEmailField('email_address'); // default: 'email'
$auth->setPasswordField('pass'); // default: 'password'
$auth->setUsernameField('alias'); // default: 'username'

// You can do it in one time with the setUsersTableFields method
$auth->setUsersTableFields([
    'usernameField' => 'username',
    'passwordField' => 'password',
    'emailField' => 'email'
]); // boolean, true if at least one field was set

// Set the column that is going to be evaluated for logging in
$auth->setLoginField('email');  // default: 'email'
```
#### Login and register
In order to log a user in, you must use the login method passing as an argument an instance of a user, whose class should be implementing the `\MDP\Auth\Autheticatable` interface. 
```php
/** @var \MDP\Auth\Auth $auth */// boolean;v

/** @var MDP\Auth\Authenticatable $user */
$auth->login($user); // logs in a user
```
Here checking credentials, loggin out, checking if there's a logged user, getting the logged user and registering.
```php
/** @var \MDP\Auth\Auth $auth */

// check credentials
$auth->check('yourField', 'youPassword'); 

// Register a user 
$auth->register('john_doe', 'john@doe.com', 'myPassword'); // bool (true if successful)

$auth->logout(); // logs out whoever was logged in

// Checks if there's someone logged in
$auth->isUserLoggedIn(); // bool

// Returns the logged user 
$auth->user(); // MDP\Auth\Authenticatable logged user | null
```