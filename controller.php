<?php

session_start();
require "DBConnect/db.php";

$page = $_GET['page'] ?? 'index';

switch ($page) {

    case 'index':
        include 'view/header.php';
        include 'view/horizontal_nav_bar.php';
        include 'view/home.php';
        break;

    case 'listingInfo':
        include 'view/header.php';
        include 'ExpandedListing/ListingInfo.php';
        break;

    case 'ViewReport':
        include 'view/reportHeader.php';
        include 'ExpandedListing/ViewReport.php';
        break;

    case 'login':
        include 'view/LoginHeader.php';
        include 'Login/login.php';
        break;

    case 'usersPage':
        include 'view/userPageHeader.php';
        include 'Users/usersPage.php';
        break;

    case 'modifyUser':
        include 'view/modifyUserHeader.php';
        include 'Users/modifyUser.php';
        break;

    case 'addUser':
        include 'view/addUserHeader.php';
        include 'Users/addUser.php';
        break;

    case 'AddSource':
        include 'view/AddSourceHeader.php';
        include 'AddListing/AddSource.php';
        break;

    case 'editListing':
        include 'view/editListing.php';
        include 'ExpandedListing/editListing.php';
        break;

    case 'logout':
        include 'Logout/logout.php';
        break;

    case 'deleteUser':
        include 'Users/deleteUser.php';
        break;

    case 'deleteListing':
        include 'ExpandedListing/deleteListing.php';
        break;

    case 'about':
        include 'view/header.php';
        include 'about/about.php';
        break;

    case 'toggleCanManage':
        include 'Users/toggleCanManage.php';
        break;

    case 'toggleModifyPermission':
        include 'Users/toggleModifyPermission.php';
        break;

    case 'manageTopics':
        include 'view/manageHeader.php';
        include 'topicsCats/manage.php';
        break;
    
    case 'removeRelatedListing':
        include 'ExpandedListing/removeRelatedListing.php';
        break;
    default:
        include 'view/header.php';
        include 'view/horizontal_nav_bar.php';
        include 'view/home.php';
        break;
}

include 'view/footer.php';
?>