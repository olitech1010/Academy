<!---------- Bread Crumb Area Start ---------->
<?php include "breadcrumb.php"; ?>
<!---------- Bread Crumb Area End ---------->

<section class="user-dashboard-area pt-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-lg-3">
                <?php include "profile_menus.php"; ?>
            </div>
            <div class="col-md-8 col-lg-9 mt-4 mt-md-0">
                <div class="my-course-1-full-body">
                    <h1><?php echo get_phrase('Bundles'); ?></h1>
                    <div class="row">
                      <?php include "user_purchase_bundle.php"; ?>
                    </div>
                </div>
            </div>
        </div>
      </div>
</section>

<?php include "course_bundle_scripts.php"; ?>