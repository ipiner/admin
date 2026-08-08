<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 后台模型基类，统一接入操作日志和隐藏删除字段。
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Model newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Model newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Model query()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperModel {}
}

namespace App\Models\System{
/**
 * 后台管理员账号模型。
 *
 * @property int $id id|自增
 * @property string $username 用户名
 * @property string $realname 姓名
 * @property string $password 密码
 * @property string $salt 加密盐值
 * @property string $avatar 头像
 * @property string $captcha_rule 验证码规则
 * @property int $login_num 登录次数
 * @property string|null $last_login_at 最后登录时间
 * @property string $last_login_ip 最后登录ip
 * @property int $v 数据版本号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $deleted_at 删除时间戳
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\System\Role> $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCaptchaRule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereLastLoginAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereLastLoginIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereLoginNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRealname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereSalt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAdmin {}
}

namespace App\Models\System{
/**
 * 后台菜单树模型。
 *
 * @property int $id id|由id生成器生成
 * @property int $pid 父id|menus.id
 * @property string $name 菜单名称
 * @property string $code 菜单编码
 * @property string $path 菜单路径|...父父id,父id,id
 * @property int $level 层级
 * @property int $sort 排序
 * @property string $type 类型|menu: 菜单; button: 按钮
 * @property int $enabled 启用|0: 否; 1: 是; 2: 是且不能禁用
 * @property int $visible 是否显示|0: 否; 1: 是
 * @property string $icon icon图标
 * @property string $route 前端路由地址
 * @property int $v 数据版本号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $deleted_at 删除时间戳
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Menu> $children
 * @property-read int|null $children_count
 * @property-read mixed $full_name
 * @property-read array $paths
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereVisible($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperMenu {}
}

namespace App\Models\System{
/**
 * @property Collection $admins
 * @property int $id id|自增
 * @property string $name 角色名称
 * @property string $remark 备注
 * @property int $v 数据版本号
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $deleted_at 删除时间戳
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\System\Menu> $menus
 * @property-read int|null $menus_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRole {}
}

namespace App\Models\System{
/**
 * @property int $id id|自增
 * @property int $role_id 角色id|roles.id
 * @property int $menu_id 菜单id|menus.id
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RoleMenu whereRoleId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperRoleMenu {}
}

namespace App\Models{
/**
 * 上传文件主表模型。
 *
 * @property int $id id|自增
 * @property string $file_id 文件uuid
 * @property int $uid 用户id|users.id
 * @property string $username 用户名
 * @property string $user_type 用户类型|user：用户；admin：管理员；console：控制台用户
 * @property string $disk 上传磁盘
 * @property string $path 路径|相对配置的disk路径
 * @property string $url url地址
 * @property string $name 显示名
 * @property string $original_name 原始名
 * @property int $size 大小
 * @property string $extension 后缀
 * @property string $mime_type mime类型
 * @property int $width 图片宽
 * @property int $height 图片高
 * @property string $ip 用户ip
 * @property array<array-key, mixed>|null $info 其它信息
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $deleted_at 删除时间戳
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Upload whereWidth($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUpload {}
}

namespace App\Models{
/**
 * @property int $id id|自增
 * @property string $file_id 文件uuid
 * @property int $uid 用户id|users.id
 * @property string $username 用户名
 * @property string $user_type 用户类型|user：用户；admin：管理员；console：控制台用户
 * @property string $path 路径|相对配置的disk路径
 * @property string $url url地址
 * @property string $name 显示名
 * @property string $original_name 原始名
 * @property int $size 大小
 * @property string $extension 后缀
 * @property string $mime_type mime类型
 * @property string $disk 上传磁盘
 * @property int $width 图片宽
 * @property int $height 图片高
 * @property int $code 返回码
 * @property string $message 返回信息
 * @property string $ip 用户ip
 * @property array<array-key, mixed>|null $info 其它信息
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereUserType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UploadLog whereWidth($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUploadLog {}
}

namespace App\Modules\Content\Models{
/**
 * 文章模型
 *
 * @property int $id id|自增
 * @property int $category_id 分类id|article_categories.id
 * @property string $title 标题
 * @property string $content 内容
 * @property int $v 数据版本号
 * @property int $created_by 创建用户id
 * @property int $updated_by 更新用户id
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $deleted_at 删除时间戳
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article whereV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticle {}
}

namespace App\Modules\Content\Models{
/**
 * 文章分类模型
 *
 * @property int $id id|由id生成器生成
 * @property int $pid 父id|article_categories.id
 * @property string $name 分类名称
 * @property string $path 分类路径|...父父id,父id,id
 * @property int $level 层级
 * @property int $sort 排序
 * @property int $v 数据版本号
 * @property int $created_by 创建用户id
 * @property int $updated_by 更新用户id
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $deleted_at 删除时间戳
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Content\Models\Article> $articles
 * @property-read int|null $articles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ArticleCategory> $children
 * @property-read int|null $children_count
 * @property-read mixed $full_name
 * @property-read array $paths
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory whereV($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ArticleCategory withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperArticleCategory {}
}

