<?php defined('BASEPATH') OR exit('No direct script access allowed');?>
<header>
	<nav role="navigation" class="navbar">
		<div class="container">
			<div>
				<a href="/" class="navbar-brand m-0" target="_self">Roonbo</a>
			</div>
		</div>
	</nav>
</header>

<div class="container">
	<div class="air-card">
		<h4 class="text-center mg-bottom-1em">Welcome to Roonbo</h4>
		<div class="content-inputs-login">
			<div class="cointainer-login">
				<div class="form-group">
					<input type="text" class="form-control pd-left-2em" id="login_username" required="required" placeholder="Username or Email">
					<span class="glyphicon air-icon-user form-control-feedback" aria-hidden="true"></span>
				</div>
				<i class="fa fa-user icon-success icon-login" aria-hidden="true"></i>
			</div>
			<div class="cointainer-login">
				<div class="form-group mg-left-0">
					<input type="text" class="form-control pd-left-2em" id="login_password" required="required" placeholder="Password">
					<span class="glyphicon air-icon-user form-control-feedback" aria-hidden="true"></span>
				</div>
				<i class="fa fa-lock icon-success icon-login" aria-hidden="true"></i>
			</div>
			<div class="container-forgot">
				<a href="#">Forgot password?</a>
			</div>
			<div class="form-group">
				<button type="button" class="btn btn-primary">Log in</button>
				<button type="button" class="btn btn-light">Sign up</button>
			</div>
		</div>
	</div>
</div>