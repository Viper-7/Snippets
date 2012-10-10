<?php
include 'lib/Bootstrap.php';

startBot('irc.freenode.com', 6667, 'myircbot', Array('#simplechannel'), TRUE);