<?php echo $__env->make('mails.header', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<div class="message">
    <p style="<?php echo e($email->fontSize); ?>">
        <?php echo $data['text']; ?>

    </p>
</div>
