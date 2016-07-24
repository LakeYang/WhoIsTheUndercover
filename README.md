# Who Is The Undercover
A Chinese game "Who is the undercover", WEB edition. Works with WeChat JSSDK. Written in PHP.

#简介
谁是卧底的WEB版本。基于微信JSSDK开发，使用PHP编写。

#功能设想
在游戏中，可以由一个人操作手机进行设置，然后轮流把手机给其他人看；也可以人手一部手机，通过网络交互进行游戏。游戏结束后可以随机出惩罚游戏。借助JSSDK可以获取用户头像与昵称，方便进行交互操作。也可以用扫一扫接口快速加入房间。

#Development Progress
In the effort of our mini team, we have almost done the single mode, But still working on UI and some bugs. Network mode is just beginning to develop, but we have written some apis first.

#Technology Introduction
We just use Javascript to control all the things in one single page. All is drawn in a canvas using CreateJS. We planned to use ajax to communicate with the server due to the lack of socket programming expierence, and we are programming to suit the needs of future transform to or adding socket programming. This application can not only be used in WeChat browser, if you set configuration in config.php, you can also use  it in other HTML5 compatible browsers.

#Hint
If you want to try it now, configure config.php right and disable WeChat unless you can read the code and setup the database. In the future we are planning to build an auto install and configuration script, so you can configure it easily like WordPress then.
