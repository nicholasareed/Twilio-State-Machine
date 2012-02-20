
<?
	if(!isset($_DarkAuth)){
		return;
	}
?>

<div id="header">
	<div id="header_content">

		<div class="row">
			<div class="span12">
				<h1>Twilio Frontend Builder</h1>
			</div>
		</div>

		<div class="topbar" data-dropdown="dropdown">
			<div class="topbar-inner">
				<div class="container">
					<ul class="nav">
						
						<li><a href="/">Home</a></li>
						<li><a href="/projects">Projects</a></li>



					</ul>

					<!-- Not Logged in -->
					<? if(!$_DarkAuth['li']){ ?>
						<ul class="nav secondary-nav">
							<li><a href="/users/signup" class="btn small primary">Sign Up</a></li>
							<li>&nbsp;</li>
							<li> <a href="/users/login" class="btn small">Member Login</a></li>
						</ul>
					<? } ?>
					<!-- Logged In -->
					<? if($_DarkAuth['li']){ ?>
						<ul class="nav secondary-nav">
							<li class="dropdown">
								<a href="#" class="dropdown-toggle"><? echo $_DarkAuth['User']['email']; ?></a>
								<ul class="dropdown-menu">
									<li><a href="/users/logout">Logout</a></li>
								</ul>
							</li>
						</ul>
					<? } ?>
					
				</div>
			</div><!-- /topbar-inner -->
		</div><!-- /topbar -->
	</div>
</div>