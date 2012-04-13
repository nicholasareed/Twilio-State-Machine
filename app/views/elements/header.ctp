
<?
	if(!isset($_DarkAuth)){
		return;
	}
?>

<!-- Simple Header -->
<div id="header">
	<div id="header_content">
		
		<div class="row">
			<div class="span3">
				<h1>
					<a href="/" style="text-decoration:none;color:black;">
						<? echo $this->Html->image('logo.png',array('width' => '40%')); ?>
					</a>
				</h1>
			</div>
			<div class="span6 offset3 text-right">
				<ul class="nav nav-pills pull-right">
					<? if($_DarkAuth['li']){ ?>

						<? if($_DarkAuth['Access']['admin']){ ?>
							<li>
								<a href="/admins"><i class="icon-align-justify"></i> Admin</a>
							</li>
						<? } ?>

						<!--
						<li>
							<a href="/pricing"><i class="icon-shopping-cart"></i> Plans and Pricing</a>
						</li>
						-->

						<? if($_DarkAuth['Access']['member']){ ?>
							<li>
								<a href="/projects"><i class="icon-align-justify"></i> Apps</a>
							</li>
							<li>
								<a href="/numbers"><i class="icon-book"></i> Numbers</a>
							</li>
						<? } ?>

						<li>
							<a href="/users/logout"><i class="icon-off"></i> Log Out</a>
						</li>

					<? } else { ?>

						<li>
							<a href="/pricing"><i class="icon-shopping-cart"></i> Plans and Pricing</a>
						</li>
						<li>
							<a href="/users/login"><i class="icon-off"></i> Log In</a>
						</li>

					<? } ?>
				</ul>
			</div>
		</div>

	</div>
</div>


<? return; ?>

<div id="header">
	<div id="header_content">

		<h1>AppSprey</h1>

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