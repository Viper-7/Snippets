<?php
    $content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec et odio sed purus porttitor rhoncus. Vivamus id tellus eu lorem posuere malesuada. Aliquam id turpis tortor, eget dignissim ante. Proin nec mattis ipsum. Proin ut odio quis nisi interdum consequat. Vestibulum sagittis est ut eros luctus consectetur. Nullam varius eros eget arcu blandit tempus. Suspendisse quis aliquet massa. Pellentesque gravida nisi eget lacus vestibulum id malesuada dolor consequat. Donec ut libero ante. Vivamus eget nisl at eros ornare tempor. Morbi elit diam, tincidunt ac gravida sed, mollis vel velit. Curabitur bibendum, libero ac elementum sollicitudin, eros urna eleifend sapien, vel consequat justo mauris vitae justo. Duis suscipit vehicula imperdiet. ';

    echo excerpt($content, 100);

    function excerpt($longString, $length, $ending = '...')
    {
        // Replace any tabs or newlines with spaces
        $content = preg_replace('/\s+/', ' ', $longString);
    
        // Wrap lines to $length, without cutting words
        $content = wordwrap($content, $length - strlen($ending), '§');

        // Grab the first line (our trimmed string)
        $content = substr($content, 0, strpos($content, '§'));

        // Add the ending only if we've shortened the string
        if($content != $longString)
            $content .= $ending;
        
        // Return the excerpt
        return $content;
    }
?>
