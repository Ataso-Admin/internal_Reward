<?php
if (extension_loaded('sqlsrv')) {
    echo "SQLSRV extension is loaded!";
} else {
    echo "SQLSRV extension is not loaded!";
}

if (extension_loaded('pdo_sqlsrv')) {
    echo "PDO_SQLSRV extension is loaded!";
} else {
    echo "PDO_SQLSRV extension is not loaded!";
}
?>
