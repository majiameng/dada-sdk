# 达达Sdk

# 达达Sdk


### 安装

```
composer require tinymeng/dada dev-master -vvv
```

> 类库使用的命名空间为`\\tinymeng\\dada`

由于让开发者友好的使用,逻辑中多处抛出异常,请做好捕捉异常以免程序报错


### 目录结构

```
.
├── example                          代码源文件目录
│   └── Dada.php                    达达逻辑处理实例
├── src                              代码源文件目录
│   └── DadaSdk.php                 达达SDK实例类
├── composer.json                    composer文件
├── LICENSE                          MIT License
└── README.md                        说明文件
```


### 配置文件样例

#### 达达配置
```
    'dada'=>[
        'app_key'=>'dada41f2c**********',
        'app_secret'=>'8eadb43e4669999***********',
        'source_id'=>'5518',//商户id
        'shop_no'=>'2004**',//门店编号
        'is_sandbox' => true,//是否是沙箱环境,上线请改成false
        'callback' => 'https://www.****.com/callback/dada',//回调地址
    ],
```

