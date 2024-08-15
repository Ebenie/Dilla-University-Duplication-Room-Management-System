<?php
function checkRole($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: ../../../403.php");
        exit();
    }
}
  ?>