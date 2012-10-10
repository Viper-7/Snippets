<?php
    $arr = array(
        array('value' => 'root',         'level' => 0),
        array('value' => 'about us',     'level' => 1),
        array('value' => 'tours',        'level' => 1),
        array('value' => 'tourist visa', 'level' => 2),
        array('value' => 'contact us',   'level' => 1)
    );

    echo nestedSetToUL($arr);

    function nestedSetToUL($arr, $rootclass = null, $valueKey='value', $levelKey='level')
    {
        $lastlevel = 0;
        $index = 0;
        
        // Create the class attribute for the root element if supplied
        if($rootclass)
            $rootclass = " class=\"{$rootclass}\"";
        
        // Open the unordered list
        $out = "<ul{$rootclass}>";

        // Loop over the array and process each element, ignoring keys
        foreach($arr as $element)
        {
            // If we've gone down one or more levels from the last element
            // Close the <ul> tags until we get to the desired level
            if($element[$levelKey] < $lastlevel)
            {
                $out .= str_repeat('</li></ul>', $lastlevel - $element[$levelKey]);
            }

            // If we're at the same level as the last element,
            // Close the last <li> and open a new one
            if($element[$levelKey] == $lastlevel)
            {
                // Don't close an <li> on the first loop
                // (when there isn't one to close yet)
                if($index) $out .= '</li>';
            }
            
            // If we've gone up one level from the last element
            // Open a <ul> tag for this level
            if($element[$levelKey] == $lastlevel + 1)
            {
                 $out .= '<ul><li>';
            }
            // If we've gone up more than one level since the last element,
            // we have no direct parent, so something's gone wrong...
            elseif($element[$levelKey] > $lastlevel)
            {
                throw new Exception("Invalid nested set, no parent for level {$element[$levelKey]}");
            } else {
                // Open the <li> tag for this element
                $out .= '<li>';
            }                

            // Output the element itself
            $out .= "{$element[$valueKey]}";
            
            // Remember the level of the node we just processed
            $lastlevel = $element[$levelKey];

            // Keep count of what element we're up to
            ++$index;
        }
        
        // Close any open <li> and <ul> tags        
        $out .= str_repeat('</li></ul>', $element[$levelKey] + 1);
        
        return $out;        
    }        

