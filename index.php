<?php
session_start();
include 'backend.php';
set_error_handler("errorHandler");
if(!isset($_SESSION['page'])){
    $_SESSION['page'] = "login";
}

//wrong input messages
if(isset($_SESSION['loginError'])){
    $loginError = $_SESSION['loginError'];
}
if(isset($_SESSION['userError'])){
    $userError = $_SESSION['userError'];
}
if(isset($_SESSION['todoError'])){
    $todoError = $_SESSION['todoError'];
}
if(isset($_SESSION['categoryError'])){
    $categoryError = $_SESSION['categoryError'];
}
$_SESSION['loginError'] = $_SESSION['userError'] = $_SESSION['todoError'] = $_SESSION['categoryError'] = "";

//more sorting stuff
if(isset($_SESSION['sortRow'])){
    if(isset($_SESSION['page'])){
        if (($_SESSION['page'] == "todos" || $_SESSION['page'] == "viewTodo") && !preg_match("/title|priority|createDate|dueDate|progress|username|name/i", $_SESSION['sortRow'])){
            $_SESSION['sortRow'] = "t.todo_ID";
        }
        if ($_SESSION['page'] == "users" && !preg_match("/username|firstName|lastName|status/i", $_SESSION['sortRow'])){
            $_SESSION['sortRow'] = "ID";
        }
        if ($_SESSION['page'] == "categories" && !preg_match("/\bname/", $_SESSION['sortRow'])){
            $_SESSION['sortRow'] = "tag_ID";
        }
    }
}elseif(isset($_SESSION['page'])){
    if ($_SESSION['page'] == "todos" || $_SESSION['page'] == "viewTodo"){
        $_SESSION['sortRow'] = "t.todo_ID";
    }
    if ($_SESSION['page'] == "users"){
        $_SESSION['sortRow'] = "ID";
    }
    if ($_SESSION['page'] == "categories"){
        $_SESSION['sortRow'] = "tag_ID";
    }
    $_SESSION['sortDir'] = "asc";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>TO-DO</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        #btn{
            color: black;
            cursor: pointer;
            text-decoration: none;
        }
        #btn:hover{
            color: darkgrey;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="backend.php?page=default">TODO</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <?php
                if(!isset($_SESSION['admin'])){
                    echo '<ul class="nav navbar-nav">
                    <li><p class="navbar-text">Please Login to continue</p></li>
                </ul>';
                }
                if(isset($_SESSION['admin']) && $_SESSION['admin'] == 0){
                    echo '<ul class="nav navbar-nav">
                    <li><a href="backend.php?page=newTodo">New TODO</a></li>
                </ul>
                <form class="navbar-form navbar-left" method="post" action="backend.php">
                    <div class="form-group">
                        <input type="text" name="searchTodos" id="searchTodos" class="form-control" placeholder="Search">
                    </div>
                    <div class="form-group">
                        <select name="column" id="column" class="form-control">
                            <option value="t.title">Title</option>
                            <option value="t.content">Content</option>
                            <option value="u.username">Creator</option>
                            <option value="c.name">Category</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                </form>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="backend.php?logout=true"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
                </ul>';
                }
                if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1){
                    if(isset($_SESSION['page']) && preg_match("/user/i", $_SESSION['page'])){
                        echo '<ul class="nav navbar-nav">
                                <li><a href="backend.php?page=newUser">New User</a></li>
                                <li><a href="backend.php?page=categories">Categories</a></li>
                            </ul>
                            <form class="navbar-form navbar-left" method="post" action="backend.php">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Search Users" name="searchUsers" id="searchUsers" >
                                </div>
                                <div class="form-group">
                                    <select name="column" id="column" class="form-control">
                                        <option value="username">Username</option>
                                        <option value="firstName">First Name</option>
                                        <option value="lastName">lastName</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                            </form>
                            <ul class="nav navbar-nav navbar-right">
                                <li><a href="backend.php?logout=true"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
                            </ul>';
                    }elseif(isset($_SESSION['page']) && preg_match("/categor/i", $_SESSION['page'])){
                        echo '<ul class="nav navbar-nav">
                                <li><a href="backend.php?page=newCategory">New Category</a></li>
                                <li><a href="backend.php?page=users">Users</a></li>
                            </ul>
                            <form class="navbar-form navbar-left" method="post" action="backend.php">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Search Categories" name="searchCategories" id="searchCategories">
                                </div>
                                
                                <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                            </form>
                            <ul class="nav navbar-nav navbar-right">
                                <li><a href="backend.php?logout=true"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
                            </ul>';
                    }
                }
                ?> <!-- different Headers for different pages/profiles-->
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
    <?php
    if(isset($_SESSION['page']) && preg_match("/login|todo|user|categor/i", $_SESSION['page'])){
        switch ($_SESSION['page']){
            //LOGIN
            case "login":
                if(!empty($loginError['error'])){
                    $_SESSION['loginError'] = "";
                    echo login($loginError['error'], $loginError['username']);
                }else{
                    echo login();
                }
                break;
            //TODOS
            case "todos":
                if(!empty($_SESSION['search'])){
                    echo todoPage("", $_SESSION['search']);
                }else{
                    echo todoPage();
                }
                break;
            case "viewTodo":
                if(!empty($_SESSION['search'])){
                    echo todoPage($_SESSION['viewTodo'], $_SESSION['search']);
                }else{
                    echo todoPage($_SESSION['viewTodo']);
                }
                break;
            case "newTodo":
                if(!empty($todoError['error'])){
                    echo todoForm($todoError['title'], $todoError['content'], $todoError['priority'], $todoError['dueDate'], $todoError['progress'], $todoError['category'], $todoError['error']);
                }else{
                    echo todoForm();
                }
                break;
            case "editTodo":
                if(!empty($todoError['error'])){
                    echo todoForm($todoError['title'], $todoError['content'], $todoError['priority'], $todoError['dueDate'], $todoError['progress'], $todoError['category'], $todoError['error']);
                }else{
                    echo editTodo($_SESSION['editTodo']);
                }
                break;
            //USERS
            case "users":
                if(!empty($_SESSION['search'])){
                    echo usersPage($_SESSION['search']);
                }else{
                    echo usersPage();
                }
                break;
            case "newUser":
                if(!empty($userError['error'])){
                    echo userForm($userError['username'], $userError['firstname'], $userError['lastname'], $userError['categories'], $userError['status'], $userError['error']);
                }else{
                    echo userForm();
                }
                break;
            case "editUser":
                if(!empty($userError['error'])){
                    echo userForm($userError['username'], $userError['firstname'], $userError['lastname'], $userError['categories'], $userError['status'], $userError['error']);
                }else{
                    echo editUser($_SESSION['editUser']);
                }
                break;
            //CATEGORIES
            case "categories":
                if(!empty($_SESSION['search'])){
                    echo categoriesPage($_SESSION['search']);
                }else{
                    echo categoriesPage();
                }
                break;
            case "newCategory":
                if(!empty($categoryError['error'])){
                    echo categoryForm($categoryError['name'], $categoryError['error']);
                }else{
                    echo categoryForm();
                }
                break;
            case "editCategory":
                if(!empty($categoryError['error'])){
                    echo categoryForm($categoryError['name'], $categoryError['error']);
                }else{
                    echo editCategory($_SESSION['editCategory']);
                }
                break;
            case "error":
                echo $_SESSION['error'];
        }
    }
    ?>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</body>
</html>