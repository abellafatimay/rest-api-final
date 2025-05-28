<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title ?? 'My Page'); ?></title>
</head>
<body>
    <h1><?php echo htmlspecialchars($heading ?? 'Welcome!'); ?></h1>
    <p>This is a sample view.</p>
    <?php if (isset($name)): ?>
        <p>Hello, <?php echo htmlspecialchars($name); ?>!</p>
    <?php endif; ?>
</body>
</html>