// jujiwuliu/pages/common/user/balance/list/index.js
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    //上面是图片轮播变量
    tabs: {
      titles: ["余额明细", "充值明细"],
      activeIndex: 0,
      sliderOffset: 0,//选显卡下划线 x轴的位置
      sliderLeft: 0,//选显卡下划线 离左边的位置
    },
    laborer: [],
    page: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
    app.util.footer(that);
    //选项卡默认选中位置
    wx.getSystemInfo({
      success: function (res) {
        var tabs_dat = that.data.tabs;
        tabs_dat.sliderLeft = (res.windowWidth / that.data.tabs.titles.length - that.data.tabs.sliderWidth) / 2,
          tabs_dat.sliderOffset = res.windowWidth / that.data.tabs.titles.length * that.data.tabs.activeIndex

        that.setData({
          tabs: tabs_dat
        });
      }
    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;

    var userType = '';
    //var userType = wx.getStorageSync('userType') //不读缓存每次重新获取
    this.pageLoading = !1;//防止多次跳转
    app.util.getUserInfo(function (userInfo) {
      //获取到用户信息后再执行下面的操作
      console.log(userInfo)
      if (typeof (userInfo) != "undefined") {
        app.memberInfo = userInfo;
        that.setData({
          memberInfo: userInfo,
        });
        app.util.request({//获取会员信息
          url: 'entry/wxapp/getcenter',
          data: {},
          method: "POST",
          success: function (res) {

            var info = res.data.data.info;
            that.setData({
              memberInfo: info
            })

            wx.setStorageSync('user_info', info)
            if (!userType) {
              if (info.type == 1) {
                wx.setStorageSync('userType', 'worker')
                app.globalData.userType = 'worker'
                that.setData({
                  userType: 'worker'
                })
              } else {
                wx.setStorageSync('userType', 'issuer')
                app.globalData.userType = 'issuer'
                that.setData({
                  userType: 'issuer'
                })
              }
            }
            //初始化底部导航
            app.util.footer(that);

          },
          fail: function (res) {
            that.onLoad()
            console.log(res)
            return false;
          }
        })
      } else {
        console.log('notlogin')
        //跳转到登陆授权页面
        if (!that.pageLoading) {
          that.pageLoading = !0;
          wx.navigateTo({
            url: '/jujiwuliu/pages/common/auth/index'
          })
        }
      }
    });

    //获取积分或者余额明细
    this.on_loadlist(1);
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
     * 页面上拉触底事件的处理函数
     */

  onReachBottom: function () {
    this.on_loadlist();
  },
  tabClick: function (e) {
    var that = this;
    var set_tabs = this.data.tabs;
    set_tabs.sliderOffset = e.currentTarget.offsetLeft;
    set_tabs.activeIndex = e.currentTarget.id
    this.setData({
      tabs: set_tabs,
    });
    this.on_loadlist(1);
    console.log(that.data.laborer)
  }, 
  on_loadlist: function (adefault = 0) {
    var that = this;
    // 页数+1
console.log(1)
    if (that.data.aloading == 1) {//如果处于加载中就退出 防止重复触发
      return false;
    }
    that.setData({
      aloading: 1
    })
    setTimeout(function () {//无论有没有加载完成 2秒后都自动退出加载状态
      that.setData({
        aloading: 0
      })
    }, 2000)

    var page = that.data.page[that.data.tabs.activeIndex] ? that.data.page[that.data.tabs.activeIndex] + 1 : 1;
    console.log(page)
    if (adefault == 1 && page != 1) {//如果是点击选项卡而且不是加载第一页加那就不加载
      return false;
    }

    app.util.request({
      'url': 'entry/wxapp/getcredit',
      'cachetime': '0',
      'data': { 'page': page, 'type': that.data.tabs.activeIndex },
      success: function (res) {
        var moment_list = that.data.laborer;
        moment_list[that.data.tabs.activeIndex] = moment_list[that.data.tabs.activeIndex] ? moment_list[that.data.tabs.activeIndex] : '';
        if (res.data.data.length > 0) {
          moment_list[that.data.tabs.activeIndex] = moment_list[that.data.tabs.activeIndex] ? moment_list[that.data.tabs.activeIndex] : [];
          for (var i = 0; i < res.data.data.length; i++) {
            moment_list[that.data.tabs.activeIndex].push(res.data.data[i]);
          }
        }
        // 设置数据
        var mor_page = that.data.page;
        console.log(moment_list);
        mor_page[that.data.tabs.activeIndex] = page;

        that.setData({
          laborer: moment_list,
          page: mor_page
        })
        that.setData({
          aloading: 0
        })
      }
    })




  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
 
})