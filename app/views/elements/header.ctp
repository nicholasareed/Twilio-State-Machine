
<?
	if(!isset($_DarkAuth)){
		return;
	}
?>

<div id="header">
	<div id="header_content">

		<div class="row">
			<div class="span12">
				<h1><? echo $_EAuth['Event']['name']; ?></h1>
			</div>
		</div>

		<div class="topbar" data-dropdown="dropdown">
			<div class="topbar-inner">
				<div class="container">
					<ul class="nav">
						
						<li class="active"><a href="/">Home</a></li>

						<? 
							// Attendees
							if($_DarkAuth['li'] && $_EAuth['Access']['attendee']){ ?>
								<li><a href="/attendees">People</a></li>
								<li><a href="/teams">Teams</a></li>
								<li><a href="/hacks/filter">Hacks</a></li>

						<? } ?>

						<!-- Judges -->
						<? if($_EAuth['Access']['judge']){ ?>

							<li><a href="/judges/round">Judging</a></li>

						<? } ?>

						<? 
							// Admin (staff?)
							if($_DarkAuth['li'] && $_EAuth['Access']['admin']){ ?>
								<li><a href="/attendees">People</a></li>
								<li><a href="/teams">Teams</a></li>
								<li><a href="/hacks/filter">Hacks</a></li>
								<li><a href="/rounds/status">Rounds</a></li>
								<li><a href="/awards">Awards</a></li>
								<li><a href="/events_tags">Tags</a></li>
						<? } ?>


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
							<? if($_EAuth['Access']['attendee']){ ?>
								<li><a href="/users/settings">My Settings</a></li>
								<li><a href="/teams/mine">My Team</a></li>
							<? } ?>
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