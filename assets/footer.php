<?php
// Generate a random number between 32 and 9999
$randomNumber = rand(32, 9999);
?>

<?php $year = date('Y'); ?>
    <footer>
        <p>Copyright &copy; DeploySmart <?php echo $year; ?></p>
        <p>Application Manager v0.<?php echo $randomNumber; ?> - Made with ♥ by Mattias Magnusson<!-- & CoPilot-->.</p>
    </footer>