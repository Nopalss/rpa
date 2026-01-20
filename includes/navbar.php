	<!--begin::Wrapper-->
	<div class="d-flex flex-column flex-row-fluid wrapper pt-24" id="kt_wrapper">
		<!--begin::Header-->
		<div id="kt_header" class="header header-fixed ">
			<!--begin::Container-->
			<div class=" container-fluid d-flex align-items-center justify-content-between">
				<div>
					<h3 class="text-capitalize"><?= $_SESSION['halaman'] ?></h3>
				</div>
				<!--begin::Header Menu Wrapper-->
				<!--end::Header Menu Wrapper-->
				<!--begin::Topbar-->
				<div class="topbar ">
					<!--begin::User-->
					<div class="topbar-item ">
						<div class="btn btn-icon btn-icon-mobile w-auto btn-clean d-flex align-items-center btn-lg px-2" id="kt_quick_user_toggle">
							<span class="text-muted font-weight-bold font-size-base d-none d-md-inline mr-1">Hi,</span>
							<span class="text-dark-50 font-weight-bolder font-size-base d-none d-md-inline mr-3"><?= $_SESSION['username'] ?></span>
							<span class="symbol symbol-lg-35 symbol-25 symbol-light-success">
								<span class="symbol-label font-size-h5 font-weight-bold text-uppercase"><?= substr($_SESSION['username'], 0, 1) ?></span>
							</span>
						</div>
					</div>
					<!--end::User-->
				</div>
				<!--end::Topbar-->
			</div>
			<!--end::Container-->
		</div>