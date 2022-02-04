<?php
session_start();
include 'backend.php';
if(!isset($_SESSION['page'])){
    $_SESSION['page'] = "login";
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
                    <li><a href="backend.php?page=newUser">New TODO</a></li>
                </ul>
                <form class="navbar-form navbar-left">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                </form>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="backend.php?logout=true"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
                </ul>';
                }
                if(isset($_SESSION['admin']) && $_SESSION['admin'] == 1){
                    echo '<ul class="nav navbar-nav">
                    <li><a href="backend.php?page=newUser">New User</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Categories <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">New Category</a></li>
                            <li><a href="#">Edit Category</a></li>
                            <li><a href="#">Delete Category</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="navbar-form navbar-left">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Search">
                    </div>
                    <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
                </form>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="backend.php?logout=true"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
                </ul>';
                }
                ?>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
    <?php
    if(isset($_SESSION['page']) && $_SESSION['page'] == "login"){
        echo login();
    }
    if(isset($_SESSION['page']) && $_SESSION['page'] == "user"){
        echo userPage();
    }
    if(isset($_SESSION['page']) && $_SESSION['page'] == "admin"){
        echo adminPage();
    }
    if(isset($_SESSION['page']) && $_SESSION['page'] == "newUser"){
        echo userForm();
    }
    if(isset($_SESSION['page']) && $_SESSION['page'] == "editUser"){
        echo editUser($_SESSION['editUser']);
    }
    ?>
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="http://netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</body>
</html>

