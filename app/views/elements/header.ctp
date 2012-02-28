
<?
	if(!isset($_DarkAuth)){
		return;
	}
?>

<div id="header">
	<div id="header_content">

		<h1>TwilioToolchain</h1>

		<div class="navbar" data-dropdown="dropdown">
			<div class="navbar-inner">
				<div class="container">
					<ul class="nav nav-tabs">
						
						<li><a href="/projects">Applications</a></li>

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