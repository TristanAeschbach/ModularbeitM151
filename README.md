###Tristan Aeschbach, I2b
# ModularbeitM151
##ToDo Website

## About
A ToDo Website created for the M151 Modularbeit. 
Each user can read and create ToDos in the categories which they have been assigned to. 
The Administrator manages the users and their categories. 

#### Website Login
Username: Admin <br>
Password: 123

#### Database Users
The Database is configured on the root user.

Database User:
```mysql
create user 'userM151'@'localhost'
	identified by '123456';

GRANT select,delete,update,insert ON m151 TO 'userM151'@'localhost';

FLUSH PRIVILEGES;
```
Database Admin:
```mysql
create user 'adminM151'@'localhost'
	identified by '123456';

GRANT ALL PRIVILEGES ON m151 TO 'adminM151'@'localhost';

FLUSH PRIVILEGES;

```
