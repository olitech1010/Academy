<!---------- Bread Crumb Area Start ---------->
<?php include "breadcrumb.php"; ?>
<!---------- Bread Crumb Area End ---------->


<style>
    .invioce-logo img{
        height: 100% !important;
    }
</style>

<!------------ Invoice section start ----->
<section class="invoice">
    <div class="container">
        <div class="print-content">
            <div class="invoice-heading">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-6">
                        <h3><?php echo strtoupper(site_phrase('invoice')); ?></h3>
                        <div class="invoice-no">
                            <h6 class="invoice-color"><?php echo get_phrase('Invoice No :')?></h6>
                            <h6> #<?php echo $bundle_payment['date_added']; ?></h6>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-6">
                        <div class="invioce-logo">
                            <a href="javascript:void(0)"><img src="<?php echo base_url('uploads/system/'.get_frontend_settings('dark_logo'));?>" alt=""></a> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="invoice-bill">
                <div class="row">
                    <div class="col-lg-6 col-md-6 col-sm-7 col-8">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-6">
                                <p><?php echo get_phrase('Billed To') ?>:</p>
                                <h6><?php echo $student_details['first_name'].' '.$student_details['last_name']; ?></h6>
                                <h6><?php echo $student_details['email']; ?></h6>
                                <h6><?php echo $student_details['address']; ?></h6>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-6 col-6">
                                <p><?php echo get_phrase('Date Of Issue') ?>:</p>
                                <h6><?php echo date('d-M-Y', $bundle_payment['date_added']) ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-5 col-4">
                        <div class="invoice-total text-end">
                            <p><?php echo get_phrase('Invoice Total') ?></p>
                            <h2><?php echo currency($bundle_payment['amount']); ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="invoice-dec">
                <div class="invoice-bill--scroll-bar">
                    <table class="table">
                        <thead class="invoice-2-table-head">
                        <tr>
                            <th class="pe-5" scope="col"><h6><?php echo get_phrase('included_courses'); ?></h6></th>
                            <th scope="col"><h6><?php echo get_phrase('instructor'); ?></h6></th>
                            <th scope="col"><h6 class="text-end"><?php echo get_phrase('total') ?></h6></th>
                        </tr>
                        </thead>
                        <tbody>
                                <th scope="row">
                                    <?php foreach(json_decode($bundle_details['course_ids']) as $course_id_of_bundle):?>
                                    <p><?php echo ' - '.$bundle_courses = $this->crud_model->get_course_by_id($course_id_of_bundle)->row('title')?></p>
                                    <?php endforeach; ?>
                                </th>
                                <td><p><?php echo $instructor_details['first_name'].' '.$instructor_details['last_name']; ?></p></td>
                                <td><p class="text-end"><?php echo currency($bundle_payment['amount']);?></p></td>
                        </tbody>
                    </table> 
                </div>
                <div class="print-btn print-d-none">
                    <a href="javascript:window.print()"><i class="fa-solid fa-print"></i><?php echo get_phrase('Print'); ?></a>
                </div>
            </div>
        </div>
    </div>
</section>
<!------------ Invoice secton end -------->