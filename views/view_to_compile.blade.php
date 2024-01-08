
        <p>some text content</p>
        <h2>
        <?php 
            $v = "this is php "
            echo $v . "script"; 
        ?>
        </h2>
        <ul>
        <?php 
           foreach($a = 1; $a < 10; $a++): 
        ?>
        <li><?php echo $a; ?></li>
        <?php endforeach; ?>
        </ul>
        