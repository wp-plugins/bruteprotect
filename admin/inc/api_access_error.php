<div class="error below-h2" id="message" style="padding-bottom: 20px;">
	<h2>API access error :(</h2>
	<h3>In order for BruteProtect to work, your site needs to be able to contact our servers.  This isn't working right now, but we're going to help you figure out the problem. Please follow the steps below:</h3>
	<ol style="max-width: 600px;">
		<li>Click <a href="http://api.bruteprotect.com/up.php" target="_blank">this link</a> (<a href="http://api.bruteprotect.com/up.php" target="_blank">http://api.bruteprotect.com/up.php</a>). <br />If this link <strong>DOES NOT</strong> work, our servers are currently offline, and you can disregard this message.  We'll be back online soon!  <br />If this link <strong>DOES</strong> work, the please continue to the next step</li>
		<li>Select all of the following text, and copy it to your clipboard:<br />
			<textarea style="width: 100%; height: 35px; font-size: 10px; line-height: 11px;"><?php echo $this->error_reporting_data ?></textarea>
		</li>
		<li>Email that text to: <a href="mailto:help@bruteprotect.com">help@bruteprotect.com</a></li>
	</ol>
	
	What does this text tell us?  It tells us the version of WordPress you are running, the error received when you try to access our servers, what web server you're using, your server's IP address, your IP address, your domain name, and your web server directory name. It does NOT give us access to any personal information, usernames, passwords, etc.
	
</div>
<br />
<h3>Want to troubleshoot this yourself?</h3>
<p>Thanks&mdash; we're really busy working on new features, so we appreciate it!</p>
<p>
	When we try to access <em>http://api.bruteprotect.com/api_check.php</em>, we're not able to get out to the server.  The following error(s) are returned:
</p>
	<ul style="margin-left: 20px;">
		<?php 
		$reporting_data = unserialize(base64_decode($this->error_reporting_data));
		$error_rows = @$reporting_data['error']->errors;
		if(is_array($error_rows)) :  foreach($error_rows as $key => $msg) : ?>
		<li><pre><strong><?php echo $key ?></strong>: <?php echo $msg[0] ?></pre></li>
		<?php endforeach; endif; ?>
	</ul>
	<p>If you aren't able to resolve this yourself, you can contact your host with this information and they should be able to help you resolve the issue.</p>
	<?php //Include our closing DIVs, because we don't want to load the rest of the page. ?>
</div>
</div>