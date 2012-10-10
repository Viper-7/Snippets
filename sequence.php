<?php
    echo getSequence('test', '/temp');

    function getSequence($name, $folder = '/tmp', $default = 0)
    {
        // Build a filename from the folder and sequence name
        $filename = realpath($folder) . '/' .  $name . '.seq';
        
        // Open the file
        $fp = fopen($filename, "a+");
        
        // Lock the file so no other processes can use it
        if (flock($fp, LOCK_EX)) {
            
            // Fetch the current number from the file
            $num = trim(stream_get_contents($fp));
            
            // Check that what we read from the file was a number
            if(!ctype_digit($num))
            {
                // If its not a number, lets reset to the default value
                $newnum = $default;
            } else {
                // If its a number, increment it
                $newnum = $num + 1;
            }
            
            // Delete the contents of the file
            ftruncate($fp,0);
            rewind($fp);
            
            // Write the new number to the file
            fwrite($fp, "{$newnum}\n");
            
            // Release the lock
            flock($fp, LOCK_UN);
        } else {
            echo "Couldn't get the lock!";
        }
        
        // Close the file
        fclose($fp);
        
        // Return the incremented number
        return $newnum;
    }
?>
