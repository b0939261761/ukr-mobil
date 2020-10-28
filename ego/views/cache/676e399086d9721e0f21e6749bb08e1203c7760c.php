<?php echo $__env->make('mails.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<div class="message">
  <table style="width: 100%; border-collapse: collapse;" border="1px">
    <tbody>
      <tr>
        <td colspan="2">
          <h3 style="text-transform: uppercase; text-align: center;">
            Номер заказа <?php echo e($data['orderId']); ?>

          </h3>
        </td>
      </tr>

      <tr>
        <td style="width: 50%;">
          <p style="text-align: center;">
            <?php echo e($data['customerName']); ?>

          </p>
        </td>
        <td style="width: 50%;">
          <p style="text-align: center;">
            Время заказа: <?php echo e(date('d.m.Y H:i:s')); ?>

          </p>
        </td>
      </tr>
  
      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            <b>Информация о доставке</b>
          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            <?php echo e($data['aboutDelivery']); ?>

          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            <?php echo e($data['recipientName']); ?>

          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            <?php echo e($data['recipientPhone']); ?>

          </p>
        </td>
      </tr>

      <tr>
        <td colspan="2" style="width: 50%;">
          <p style="text-align: center;">
            <?php echo e($data['shippingAddress']); ?>

          </p>
        </td>
      </tr>
    </tbody>
  </table>

  <br>
  <br>

  <table style="width: 100%; border-collapse: collapse;" border="1px">
    <thead>
      <tr>
        <th colspan="4">
          <h3 style="text-align: center;">
            Заказ
          </h3>
        </th>
      </tr>
      <tr>
        <th>Название</th>
        <th>Цена</th>
        <th>Количество</th>
        <th>Итого</th>
      </tr>
    </thead>
    <tbody>
      <?php $__currentLoopData = $data['productList']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td style="text-align: center;">
            <?php echo e($item['name']); ?>

          </td>
          <td style="text-align: center;">
            <?php echo e($item['priceFormat']); ?>

          </td>
          <td style="text-align: center;">
            <?php echo e($item['quantity']); ?>

          </td>
          <td style="text-align: center;">
            <?php echo e($item['totalFormat']); ?>

          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td colspan="4" style="text-align: right;">
          <b>Итого:</b> <?php echo e($data['total']); ?>

        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php /**PATH /home/admin/web/ukrmob.reality.sh/public_html/ego/views/mails/new-order.blade.php ENDPATH**/ ?>