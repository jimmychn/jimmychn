<?php
// 檢查使用者是否有權限
function checkPermission($pdo, $userId, $permissionName) {
    $sql = "SELECT COUNT(*) FROM RolePermission rp
            JOIN User u ON rp.RoleID = u.RoleID
            JOIN Permission p ON rp.PermissionID = p.PermissionID
            WHERE u.UserID = ? AND p.PermissionName = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $permissionName]);
    return $stmt->fetchColumn() > 0;
}
function hasPermission($pdo, $userId, $permissionName) {
    $sql = "SELECT COUNT(*) FROM RolePermissions rp
            JOIN Users u ON rp.RoleID = u.RoleID
            JOIN Permissions p ON rp.PermissionID = p.PermissionID
            WHERE u.UserID = ? AND p.PermissionName = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $permissionName]);
    return $stmt->fetchColumn() > 0;
}

?>