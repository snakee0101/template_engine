<p>
<?php
    $a = 7**2;
    $b = 3;
    $arr = [5, 5, 5, 'abc'];
    echo "The result of calculation is: " . $a + $b;
?>
</p>
<p>This paragraph contains the value of 'abc' GET-parameter <?php echo $_GET['abc']; ?></p>
<h2>Next is print_r()</h2>
<?php print_r($arr); ?>