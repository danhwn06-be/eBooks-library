<?php require APP_ROOT . "/app/views/inc/header.php"; ?>

<div class="container main-container">

  <div>
    <div class="fa-solid fa-house">    </div> <b class="bd-home-detail"><a href="<?php echo URL_ROOT; ?>">Home</a>
    /
    <a href="<?php echo URL_ROOT; ?>book/">Books</a> /</b>
    <span class="tile-bx"><?php echo $data['book']['title']; ?></span>
  </div>

  <div class="bd-wrapper">

    <div class="bd-image-box">
      <img src="<?php echo URL_ROOT . '/images/books/' . ($data['book']['image_url'] ? $data['book']['image_url'] : 'default-book.jpg'); ?>"
           alt="<?php echo $data['book']['title']; ?>">
    </div>

    <div class="bd-card">

      <div class="bd-info">

        <h2 class="bd-title"><?php echo strtoupper($data['book']['title']); ?></h2>

        <p class="bd-sub">
          by <strong><?php echo $data['book']['author']; ?></strong>
          Category: <strong><?php echo $data['book']['category_name']; ?></strong>
        </p>

        <div class="bd-versions">
          <?php echo $data['book']['available_copies']; ?> versions available /
          <?php echo $data['book']['total_copies']; ?> copies

        </div>

        <div class="bd-info-boxes">
          <div class="bd-info-box">
            <span>Publish Date</span>
            <strong><?php echo htmlspecialchars($data['book']['publication_year']); ?></strong>
          </div>
          <div class="bd-info-box">
            <span>Publisher</span>
            <strong><?php echo htmlspecialchars($data['book']['publisher']); ?></strong>
          </div>
          <div class="bd-info-box">
            <span>ISBN</span>
            <strong><?php echo htmlspecialchars($data['book']['isbn']); ?></strong>
          </div>
        </div>

        <?php if ($data['book']['available_copies'] > 0): ?>
          <a href="<?php echo URL_ROOT; ?>/book/reserve/<?php echo $data['book']['book_id']; ?>"
             class="bd-btn-reserve">Reservations</a>
        <?php endif; ?>

      </div>

    </div>

  </div>

  <div class="bd-desc">
    <p><?php echo nl2br($data['book']['description']); ?></p>
  </div>

</div>

<?php require APP_ROOT . "/app/views/inc/footer.php"; ?>