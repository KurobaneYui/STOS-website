
<header class="topbar">
	<nav class="navbar top-navbar navbar-toggleable-sm navbar-light">
		<!-- ============================================================== -->
		<!-- Logo -->
		<!-- ============================================================== -->
		<div class="navbar-header">
			<a class="navbar-brand" href="/index.html">
				<!-- Logo icon --><b>
					<!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->    
					<!-- Light Logo icon -->
					<img src="/assets/images/STOS.png" alt="homepage" class="light-logo" />
					<!-- <img src="./assets/images/logo-light-icon.png" alt="homepage" class="light-logo" /> -->
				</b>
				<!--End Logo icon -->
				<!-- Logo text --><span style="color:aliceblue">

				 <!-- Light Logo text -->  学风督导队  
				 <!-- <img src="./assets/images/logo-light-text.png" class="light-logo" alt="homepage" /> -->
				</span> </a>
		</div>
		<!-- ============================================================== -->
		<!-- End Logo -->
		<!-- ============================================================== -->
		<div class="navbar-collapse">
			<!-- ============================================================== -->
			<!-- toggle and nav items -->
			<!-- ============================================================== -->
			<ul class="navbar-nav mr-auto mt-md-0">
				<!-- This is  -->
				<li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
				<!-- ============================================================== -->
				<!-- Search -->
				<!-- ============================================================== -->
				<li class="nav-item hidden-sm-down search-box"> <a class="nav-link hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-search"></i></a>
					<form class="app-search">
						<input type="text" class="form-control" placeholder="Search & enter"> <a class="srh-btn"><i class="ti-close"></i></a> </form>
				</li>
			</ul>
			<!-- ============================================================== -->
			<!-- User profile and search -->
			<!-- ============================================================== -->
			<ul class="navbar-nav my-lg-0">
			<li class="nav-item dropdown"><a href=<?php if($person->work_info()["权限"]=='1') echo "'#'";else echo "'http://132.232.231.109/document/2019学风督导队手册.pdf'";?> class="nav-link text-muted waves-effect waves-dark" aria-haspopup="true" aria-expanded="false">督导队手册</a></li>
				<!-- ============================================================== -->
				<!-- Profile -->
				<!-- ============================================================== -->
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onClick="confir()"><?php echo $person->xinming; ?></a>
				</li>
			</ul>
		</div>
	</nav>
</header>
<script>
	function confir(){
		if(confirm("要登出吗？是：登出；否：返回个人主页"))
			window.location.href="/log/logout.php";
		else
			window.location.href="/personal/index.php";
	}
</script>
