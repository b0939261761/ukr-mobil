<?php echo $__env->make('mails.header', \Illuminate\Support\Arr::except(get_defined_vars(), array('__data', '__path')))->render(); ?>

<div class="message">
    Товар
    <b><?php echo e($data['product']); ?></b>
    уже на сайте! Поспешите купить его!
</div>
