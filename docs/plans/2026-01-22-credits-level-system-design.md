# BitsMesh 积分等级系统设计文档

**日期**: 2026-01-22
**状态**: 🚧 设计中

## 概述

为 BitsMesh 主题实现完整的积分（鸡腿）和等级系统，包括：
- 用户等级 Lv1-Lv6
- 积分获取机制（发帖、评论、签到、投喂）
- 三个新页面（等级进度、积分记录、签到）
- 侧边栏功能完善

## 等级与积分规则

### 等级划分

| 等级 | 所需积分 | 升级增量 |
|------|---------|---------|
| Lv1 | 0 | - |
| Lv2 | 100 | +100 |
| Lv3 | 1000 | +900 |
| Lv4 | 2500 | +1500 |
| Lv5 | 5000 | +2500 |
| Lv6 | 10000 | +5000 |

### 积分获取规则

| 行为 | 积分 | 每日上限 | 备注 |
|------|------|---------|------|
| 发帖 | +5 | 20 | 每天最多 4 帖获得积分 |
| 评论 | +1 | 20 | 每天最多 20 条评论获得积分 |
| 签到 | +1~20 | 20 | 随机获得 |
| 被投喂 | +N | 无 | 其他用户赠送 |

### 投喂规则

- 每个用户每天有 1 次免费投喂额度
- 投喂数量可自定义（如：1、5、10 鸡腿）
- 投喂需在帖子详情页操作

## 数据库设计

### 方案：使用现有 Points 字段 + UserMeta + 新表

**1. GDN_User 表（现有）**
- 利用现有 `Points` 字段存储积分（鸡腿）

**2. GDN_UserMeta 表（现有）**
- 存储每日统计和签到状态

```
Key 格式:
- Credits.DailyPostCredits.{YYYYMMDD}    每日发帖获得积分
- Credits.DailyCommentCredits.{YYYYMMDD} 每日评论获得积分
- Credits.DailyFeedCount.{YYYYMMDD}      每日投喂次数
- Credits.LastCheckIn                     最后签到日期
- Credits.ConsecutiveCheckIn              连续签到天数
```

**3. GDN_CreditLog 表（新建）**

积分变动记录表：

```sql
CREATE TABLE GDN_CreditLog (
    LogID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    Amount INT NOT NULL,           -- 积分变动（正/负）
    Balance INT NOT NULL,          -- 变动后余额
    Type VARCHAR(20) NOT NULL,     -- 类型：post/comment/checkin/feed_give/feed_receive
    RelatedID INT NULL,            -- 关联 ID（如 DiscussionID、CommentID）
    RelatedUserID INT NULL,        -- 关联用户（投喂场景）
    Note VARCHAR(255) NULL,        -- 备注
    DateInserted DATETIME NOT NULL,
    INDEX idx_user_date (UserID, DateInserted),
    INDEX idx_type (Type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**4. GDN_Role 表修改**

新增等级角色（RoleID 使用空闲 ID）：

```sql
INSERT INTO GDN_Role (RoleID, Name, Description, Type, Sort, Deletable, CanSession, PersonalInfo) VALUES
(64, 'Lv1', '一级会员', 'member', 10, 0, 1, 0),
(65, 'Lv2', '二级会员', 'member', 11, 0, 1, 0),
(66, 'Lv3', '三级会员', 'member', 12, 0, 1, 0),
(67, 'Lv4', '四级会员', 'member', 13, 0, 1, 0),
(68, 'Lv5', '五级会员', 'member', 14, 0, 1, 0),
(69, 'Lv6', '六级会员', 'member', 15, 0, 1, 0);
```

## URL 路由

| 页面 | 路由 | 控制器 |
|------|------|--------|
| 等级进度 | `/progress` | CreditsController::progress() |
| 积分记录 | `/credit` | CreditsController::credit() |
| 签到 | `/board` | CreditsController::board() |
| 签到 API | `/credit/checkin` | CreditsController::checkIn() |
| 投喂 API | `/credit/feed` | CreditsController::feed() |

## 页面设计

### 1. /progress - 等级进度页面

**布局：**
```
┌─────────────────────────────────────────┐
│ 等级进度                                 │
├─────────────────────────────────────────┤
│ ┌─────────────────────────────────────┐ │
│ │ 当前等级: Lv3                        │ │
│ │ 当前鸡腿: 1,234                      │ │
│ │                                       │ │
│ │ [████████████░░░░░] 23.4%            │ │
│ │ 距离 Lv4 还需 1,266 鸡腿              │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 鸡腿获取规则                         │ │
│ ├─────────────────────────────────────┤ │
│ │ • 发帖: +5 鸡腿/帖 (每日上限20)      │ │
│ │ • 评论: +1 鸡腿/条 (每日上限20)      │ │
│ │ • 签到: +1~20 鸡腿 (随机)            │ │
│ │ • 被投喂: 无上限                      │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 等级升级表                           │ │
│ ├─────────────────────────────────────┤ │
│ │ Lv1 → Lv2: 100 鸡腿                  │ │
│ │ Lv2 → Lv3: 1000 鸡腿                 │ │
│ │ Lv3 → Lv4: 2500 鸡腿                 │ │
│ │ Lv4 → Lv5: 5000 鸡腿                 │ │
│ │ Lv5 → Lv6: 10000 鸡腿                │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

### 2. /credit - 积分记录页面

**布局：**
```
┌─────────────────────────────────────────┐
│ 鸡腿账簿                 当前: 1,234 🍗  │
├─────────────────────────────────────────┤
│ ┌───────┬──────────┬───────┬──────────┐ │
│ │ 时间   │ 类型     │ 变动  │ 余额      │ │
│ ├───────┼──────────┼───────┼──────────┤ │
│ │ 10:30 │ 发帖     │ +5    │ 1,234    │ │
│ │ 10:25 │ 评论     │ +1    │ 1,229    │ │
│ │ 09:00 │ 签到     │ +12   │ 1,228    │ │
│ │ 昨天  │ 被投喂   │ +10   │ 1,216    │ │
│ └───────┴──────────┴───────┴──────────┘ │
│                                         │
│ ← 上一页  第 1 页 / 共 5 页  下一页 →    │
└─────────────────────────────────────────┘
```

### 3. /board - 签到页面

**布局：**
```
┌─────────────────────────────────────────┐
│ 每日签到                                 │
├─────────────────────────────────────────┤
│ ┌─────────────────────────────────────┐ │
│ │ 已连续签到 7 天                       │ │
│ │                                       │ │
│ │ [今日已签到 ✓]  获得 15 鸡腿          │ │
│ │     或                                │ │
│ │ [立即签到]                            │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ 签到日历                             │ │
│ ├─────────────────────────────────────┤ │
│ │   日 一 二 三 四 五 六               │ │
│ │              1  2  3  4              │ │
│ │   5  6  7  8  9 10 11               │ │
│ │  12 13 14 15 16 17 18               │ │
│ │  19 20 21[22]23 24 25               │ │
│ │  26 27 28 29 30 31                  │ │
│ │  (带圆圈的日期表示已签到)             │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

## 侧边栏修改

### 当前状态（第 38-54 行）

```smarty
<div class="bits-user-actions">
    <span title="{t c='Check In' d='签到'}">  <!-- 无链接 -->
        <svg class="iconpark-icon"><use href="#plan"></use></svg>
    </span>
    ...
</div>
```

### 修改后

```smarty
<div class="bits-user-actions">
    <a href="{url '/board'}" title="{t c='Check In' d='签到'}">
        <svg class="iconpark-icon"><use href="#plan"></use></svg>
    </a>
    ...
</div>
```

### 等级/鸡腿链接修改（第 59-71 行）

**当前：**
```smarty
<a href="{$SidebarUserProfileUrl}">  <!-- 等级链接到个人主页 -->
    <span>{t c='Level' d='等级'} Lv 1</span>
</a>
<a href="#">  <!-- 鸡腿无链接 -->
    <span>{t c='Credits' d='鸡腿'} 0</span>
</a>
```

**修改后：**
```smarty
<a href="{url '/progress'}">
    <span>{t c='Level' d='等级'} Lv {$SidebarUserLevel}</span>
</a>
<a href="{url '/credit'}">
    <span>{t c='Credits' d='鸡腿'} {$SidebarUserCredits|number_format}</span>
</a>
```

## 文件清单

### 新建文件

| 文件 | 用途 |
|------|------|
| `plugins/Credits/class.credits.plugin.php` | 积分系统插件主文件 |
| `plugins/Credits/models/class.creditmodel.php` | 积分模型 |
| `plugins/Credits/controllers/class.creditscontroller.php` | 积分控制器 |
| `plugins/Credits/views/progress.php` | 等级进度页面视图 |
| `plugins/Credits/views/credit.php` | 积分记录页面视图 |
| `plugins/Credits/views/board.php` | 签到页面视图 |
| `plugins/Credits/design/credits.css` | 积分系统样式 |
| `plugins/Credits/js/credits.js` | 前端交互（签到、投喂） |

### 修改文件

| 文件 | 修改内容 |
|------|---------|
| `themes/bitsmesh/views/modules/sidebar-welcome.tpl` | 等级/鸡腿链接、签到按钮 |
| `themes/bitsmesh/class.bitsmesh.themehooks.php` | 注入用户等级和积分数据 |
| `themes/bitsmesh/views/discussion/helper_functions.php` | 添加投喂按钮 |

## 实施步骤

### 阶段一：基础架构
1. [ ] 创建 Credits 插件目录结构
2. [ ] 创建 GDN_CreditLog 表
3. [ ] 添加 Lv1-Lv6 用户角色
4. [ ] 实现 CreditModel 核心方法

### 阶段二：积分获取
5. [ ] 实现发帖积分（钩子：DiscussionModel_AfterSaveDiscussion）
6. [ ] 实现评论积分（钩子：CommentModel_AfterSaveComment）
7. [ ] 实现签到功能（/board 页面 + API）
8. [ ] 实现等级自动升级

### 阶段三：页面开发
9. [ ] 创建 /progress 等级进度页面
10. [ ] 创建 /credit 积分记录页面
11. [ ] 创建 /board 签到页面
12. [ ] 添加 CSS 样式

### 阶段四：集成
13. [ ] 修改侧边栏模板
14. [ ] 注入用户等级和积分数据
15. [ ] 实现投喂功能
16. [ ] 测试验证

## 技术细节

### 等级计算

```php
class CreditModel {
    const LEVEL_THRESHOLDS = [
        1 => 0,
        2 => 100,
        3 => 1000,
        4 => 2500,
        5 => 5000,
        6 => 10000
    ];

    public static function calculateLevel($credits) {
        $level = 1;
        foreach (self::LEVEL_THRESHOLDS as $lv => $threshold) {
            if ($credits >= $threshold) {
                $level = $lv;
            }
        }
        return $level;
    }

    public static function getProgressToNextLevel($credits) {
        $currentLevel = self::calculateLevel($credits);
        if ($currentLevel >= 6) {
            return ['percentage' => 100, 'needed' => 0, 'nextLevel' => 6];
        }

        $currentThreshold = self::LEVEL_THRESHOLDS[$currentLevel];
        $nextThreshold = self::LEVEL_THRESHOLDS[$currentLevel + 1];
        $range = $nextThreshold - $currentThreshold;
        $progress = $credits - $currentThreshold;

        return [
            'percentage' => round(($progress / $range) * 100, 1),
            'needed' => $nextThreshold - $credits,
            'nextLevel' => $currentLevel + 1
        ];
    }
}
```

### 每日限制检查

```php
public function canEarnCredits($userID, $type) {
    $today = date('Ymd');
    $metaKey = "Credits.Daily{$type}Credits.{$today}";

    $earned = Gdn::userMetaModel()->getUserMeta($userID, $metaKey, 0);
    $earned = reset($earned) ?: 0;

    $limits = [
        'Post' => 20,
        'Comment' => 20
    ];

    return $earned < ($limits[$type] ?? PHP_INT_MAX);
}
```

## 待确认事项

1. ✅ 使用现有 Points 字段存储积分
2. ✅ 等级使用独立用户组（而非现有 Member 角色）
3. ✅ 每日限制通过 UserMeta 实现
4. ⏳ 投喂功能的 UI 位置（帖子详情页底部）

---

**创建日期**: 2026-01-22
**设计者**: Claude AI
