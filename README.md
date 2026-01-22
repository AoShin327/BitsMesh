# BitsMesh

## 安装

### 首次安装

1. 克隆仓库
```bash
git clone https://github.com/AoShin327/BitsMesh.git
```

2. 配置 Web 服务器指向项目目录

3. 设置目录权限
```bash
chmod -R 755 cache/ uploads/ conf/
```

4. 访问网站，自动跳转安装向导完成配置

### 后续更新

```bash
cd BitsMesh
git pull origin main
```

如有数据库结构变更，访问 `/utility/update` 完成升级。
