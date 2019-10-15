// jujiwuliu/pages/issuer/index/index.js
var dateTimePicker = require('../../../resource/js/dateTimePicker.js');
var app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    siteinfo: app.siteInfo,
    type: 0,
    static: 1,
    price: 15,
    prices: 0,
    count: '',
    nums: '',
    starttime: '',
    images: [],
    total_price: 0, //订单不含保证金单人总价 
    count_price: 0, //订单不含保证金总价
    bond: 0, //保证金
    all_price: 0, //订单总金额+保证金
    startYear: 2000,
    endYear: 2050,
    sexArray: ['不限', '男', '女'],
    cargoArray: ['重货', '泡货', '重泡货'],
    timeUintArray: ['天', '小时', '完成即止'],
    sexIndex: 0,
    cargoIndex: 0,
    timeUintIndex: 0,
    navList: ["装卸工 ", "临时工"],
    page: [],
    laborer: [],
    aloading: 0,
    attachurl: app.siteInfo.attachurl,
    classify: {},//分类
    setting: app.Setting,
    inputMarBot: false,
    onFocus: false,    //textarea焦点是否选中
    isShowText: false, //控制显示 textarea 还是 text
    remark: '',        //用于存储textarea输入内容
    modalCheckUser: !1,
    check_user_tem: [],
    check_userids: [],
    check_users: [],
    user_list: [
      { 'id': 1, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 2, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 3, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 4, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 5, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 6, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 7, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 8, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 9, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 10, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 11, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 12, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 13, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 },
      { 'id': 14, avatar: 'https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL14xhicAxUuaUs6PWGCcPRQuu29WxstX9HOvj5XZPicsPqbPsk72grk0lgDKrj7bmibIAsee89nkhlA/132', 'nickname': 'edison', 'active': 0 }
    ],
    lat: 0,
    lng: 0,
    latitude: 0,
    longitude: 0,
    provinces: [],
    provincesIndex: 0,
    citys: [],
    citysIndex: 0,
    districts: [],
    districtsIndex: 0,
    //modalPayBtns:!1,//支付选项
    get_address_show: 0,
    paytype: 0, //支付类型，1微信，2余额
    payData: {
      modalPayBtns: !1, //支付选择
      bond: 0, //保证金
      credit_enough: 0 //余额
    }
  },
  check_modalpay: function () {
    this.setData({
      //modalPayBtns:!0
      'payData.modalPayBtns': !0
    })
  },
  hide_paybtns() {
    this.setData({
      //modalPayBtns: !1
      'payData.modalPayBtns': !1
    })
  },
  payTypeChange: function (e) {
    console.log(e.detail.value)
    this.setData({
      paytype: e.detail.value
    })
  },
  onShowTextare() {//显示textare
    this.setData({
      isShowText: true,
      onFocus: false
    })
  },
  onShowText() {       //显示text
    this.setData({
      isShowText: false,
      onFocus: false
    })
  },
  onRemarkInput(event) {               //保存输入框填写内容
    var value = event.detail.value;
    this.setData({
      description: value,
    });
    this.setData({
      isShowText: false,
      onFocus: false
    })
  },
  // 图片上传
  bindChooiceProduct: function () {
    var that = this;

    wx.chooseImage({
      count: 9,  //最多可以选择的图片总数  
      sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有  
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        // 返回选定照片的本地文件路径列表，tempFilePath可以作为img标签的src属性显示图片  
        var tempFilePaths = res.tempFilePaths;
        console.log(tempFilePaths);
        //启动上传等待中...  
        wx.showToast({
          title: '正在上传...',
          icon: 'loading',
          mask: true,
          duration: 1000
        });

        that.setData({//每次上传清除以前上传的图片
          images: []
        })
        tempFilePaths.forEach((file_path, index) => {
          wx.uploadFile({
            url: app.util.getUrl('entry/wxapp/imgupload'),
            filePath: file_path,
            name: 'file',
            formData: {
              file: file_path,
            },
            header: {
              "Content-Type": "multipart/form-data"
            },
            success(res) {
              console.log(res);
              var info = JSON.parse(res.data);
              if (info.errno == 0) {
                info = info.data;
                var info_images = that.data.images;

                info_images.push(info.file_name);

                that.setData({
                  images: info_images
                })
              } else {
                var message = info.message;
                wx.showModal({
                  title: '获取失败',
                  content: message,
                  showCancel: false
                });
                return false;
              }
            }
          })
        })


      }
    });
  },
  //打开指定选择框
  bindChooiceAppoint: function () {
    var that = this;
    if (typeof (that.data.user_list) == 'object' && that.data.user_list.length > 0) {

      that.setData({//把勾选之前的数据缓存起来
        check_user_tem: that.data.user_list
      })
      that.setData({
        modalCheckUser: !0,
      })
    } else {
      wx.showModal({
        title: '系统提示',
        content: '未查询到已开工的历史工人!',
        showCancel: false,
        success: function (res) {
          if (res.confirm) {
          }
        }
      })
    }
  },
  //点击选中窗口中相应的人员
  bindAddAppoint: function (e) {
    var that = this;
    var user_list = that.data.user_list;
    user_list[e.currentTarget.dataset.index].active = user_list[e.currentTarget.dataset.index].active == 1 ? 0 : 1;
    that.setData({
      user_list: user_list,
    })
  },

  //点击删除队列中相应的人员
  bindRemoveAppoint: function (e) {
    var that = this;
    var user_list = that.data.user_list;
    var check_users = that.data.check_users;
    var check_userids = that.data.check_userids;

    user_list[e.currentTarget.dataset.index].active = 0;
    check_users.splice(e.currentTarget.dataset.index, 1);
    check_userids.splice(check_userids.indexOf(user_list[e.currentTarget.dataset.index].id), 1);
    that.setData({
      user_list: user_list,
      check_users: check_users,
      check_userids: check_userids,
    })

  },

  //货物类型
  cargoPickerChange: function(e){
    this.setData({
      cargoIndex: e.detail.value
    })
  },
  //关闭选择窗口
  colseCheckAppoint: function () {
    var that = this;
    //还原勾选的数据
    that.setData({//还原为勾选前的数据
      user_list: that.data.check_user_tem,
      modalCheckUser: !1
    })

  },
  //确认选择人员
  submitCheckAppoint: function () {
    var that = this;
    //整理选中数据到页面,整理暂存提交数据
    var check_userids = [], check_users = [];

    that.data.user_list.forEach(function (user, index) {
      if (user.active == 1) {
        check_users[index] = user;
        check_userids.push(user.id);
      }
    })
    console.log(check_userids);
    that.setData({
      check_users: check_users,
      check_userids: check_userids,
      modalCheckUser: !1
    })

  },
  //点击删除队列中相应的图片
  bindRemoveImg: function (e) {
    var that = this;
    var del_index = e.currentTarget.dataset.index;
    var info_images = that.data.images;
    info_images.splice(del_index, 1);//从id开始删除一个元素 其实就是把自己删除了
    that.setData({
      images: info_images
    })
    console.log(del_index);
  },

  getNav: function (e) {
    var type = e.currentTarget.dataset.index
    var setting = wx.getStorageSync('setting_set')
    var bond = 0
    var count_price = 0
    this.setData({
      type: type
    })
    if (type == 1) {
      //count_price = 200 * this.data.count * this.data.nums
      var temporary_light = this.data.setting_set.temporary_light
      count_price = temporary_light * this.data.count * this.data.nums
      bond = count_price * setting.data.security / 100,
        this.setData({
          price: temporary_light,
          total_price: temporary_light * this.data.count,
          count_price: count_price,
          bond: bond,
          all_price: parseFloat(count_price) + parseFloat(bond),
          static: 1
        })
    } else {
      var dock_light = this.data.setting_set.dock_light
      //count_price = 1 * this.data.count * this.data.nums
      count_price = dock_light * this.data.count
      bond = count_price * setting.data.security / 100,
        this.setData({
          price: dock_light,
          total_price: dock_light * this.data.count,
          count_price: count_price,
          bond: bond,
          all_price: parseFloat(count_price) + parseFloat(bond),
          static: 1
        })
    }
  },

  //时间单位
  timeUintChange: function(e){
    var that = this;
    var timeuint = e.detail.value;
    that.setData({
      timeUintIndex: timeuint
    })
  },

  getStatis: function (e) {
    var bond = 0  //佣金
    var setting = wx.getStorageSync('setting_set')
    var type = this.data.type
    var price = 0;
    var nums = this.data.nums == '' ? 0 : this.data.nums //人数
    var count = this.data.count == '' ? 0 : this.data.count //数量,天数
    var total_price = 0
    var count_price = 0
    if(e.currentTarget.dataset.index != 4){
      price = e.currentTarget.dataset.price
      total_price = price * count
      count_price = type == 1 ? price * count * nums : price * count
    }else{
      if(type == 1){
        price = this.data.prices == 0 || nums == 0 || count == 0 ? 0 : this.data.price / nums / count
        total_price = nums > 0 ? this.data.prices : 0
      }else{
        price = this.data.prices == 0 || count == 0 ? 0 : this.data.price / count
        total_price = this.data.prices
      }
      count_price = this.data.prices
    }
    bond = count_price * setting.data.security / 100 
    this.setData({
      static: e.currentTarget.dataset.index,
      price: price,
      total_price: total_price,
      count_price: count_price,
      bond: bond,
      all_price: parseFloat(bond) + parseFloat(count_price) //总支付金额 保证金+发布金额
    })
  },

  address: function (e) {
    var that = this
    var value = e.detail.value;
    that.setData({
      address: value
    })
  },

  description: function (e) {
    var that = this
    var value = e.detail.value;
    that.setData({
      description: value
    })
  },

  getCount: function (e) {
    var that = this
    var value = e.detail.value;
    var bond = 0;
    var type = that.data.type
    var setting = wx.getStorageSync('setting_set')
    if (value) {
      if (that.data.static != 4) {
        var total_price = value * that.data.price
        var count_price = type == 1 ? value * that.data.price * that.data.nums : value * that.data.price
        bond = count_price * setting.data.security / 100
        that.setData({
          count: value,
          total_price: total_price,
          count_price: count_price,
          bond: bond,
          all_price: parseFloat(bond) + parseFloat(count_price)
        })
      } else {
        that.setData({
          count: value
        })
      }
    }
  },

  getPices: function (e) {
    var that = this
    var value = e.detail.value;
    var setting = wx.getStorageSync('setting_set')
    var bond = 0;
    if (value) {
      var count_price = value
      bond = count_price * setting.data.security / 100
      that.setData({
        prices: value,
        total_price: value,
        count_price: count_price,
        bond: bond,
        all_price: parseFloat(bond) + parseFloat(count_price)
      })
    }
  },

  getNums: function (e) {
    var that = this
    var value = e.detail.value;
    var bond = 0;
    var type = that.data.type
    var setting = wx.getStorageSync('setting_set')
    if (value) {
      if (that.data.static == 4) {
        var count_price = that.data.prices
        bond = count_price * setting.data.security / 100
        that.setData({
          nums: value,
          count_price: count_price,
          bond: bond,
          all_price: parseFloat(count_price) + parseFloat(bond)
        })
      } else {
        var count_price = type == 1 ? that.data.price * that.data.count * value : that.data.price * that.data.count
        bond = count_price * setting.data.security / 100
        that.setData({
          nums: value,
          count_price: count_price,
          bond: bond,
          all_price: parseFloat(bond) + parseFloat(count_price)
        })
      }
    }
  },

  check_pay: function (e) {
    var that = this;
    var pay_data = that.data.pay_data;
    var order_id = that.data.order_id;
    var paytype = that.data.paytype

    if (paytype == 1) {
      //发起支付
      wx.requestPayment({
        'timeStamp': pay_data.timeStamp,
        'nonceStr': pay_data.nonceStr,
        'package': pay_data.package,
        'signType': 'MD5',
        'paySign': pay_data.paySign,
        'success': function (res) {
          //执行支付成功提示
          /*
          wx.showToast({
            title: '请输入用工地点',
            icon: 'warn',
            image: '/jujiwuliu/resource/images/error.png',
            duration: 1000
          });
          */
          wx.navigateTo({
            url: '/jujiwuliu/pages/issuer/publish/detail/index?issuer_id=' + order_id
          });
        },
        'fail': function (res) {
          // backApp()
        }
      })
    }
    if (paytype == 2 && that.data.payData.credit_enough == 1) {
      var formid = wx.getStorageSync("formId")
      //发起余额支付
      app.util.request({
        url: 'entry/wxapp/CreditIssusrPay',
        data: {
          order_id: order_id,
          formid: formid
        },
        success: function (res) {
          that.setData(that.data.dataTmp);

          //重新初始化时间
          var obj = dateTimePicker.dateTimePicker(that.data.startYear, that.data.endYear);
          var lastArray = obj.dateTimeArray;
          var lastTime = obj.dateTime;
          var dateTime = obj.dateTime;

          that.setData({
            dateTime: obj.dateTime,
            dateTimeArray: obj.dateTimeArray,
            starttime: obj.dateTimeArray[0][dateTime[0]] + '-' + obj.dateTimeArray[1][dateTime[1]] + '-' + obj.dateTimeArray[2][dateTime[2]] + ' ' + obj.dateTimeArray[3][dateTime[3]] + ':' + obj.dateTimeArray[4][dateTime[4]] + ':' + obj.dateTimeArray[5][dateTime[5]],
          });


          wx.navigateTo({
            url: '/jujiwuliu/pages/issuer/publish/detail/index?issuer_id=' + order_id
          });
        }
      })
    }

  },
  submit: function (e) {
    var that = this;
    var formid = e.detail.formId;
    if (that.data.static == 4) {
      if (!that.data.prices) {
        wx.showToast({
          title: '请输入一口价',
          icon: 'warn',
          image: '/jujiwuliu/resource/images/error.png',
          duration: 1000
        });
        return false;
      }
    }
    if (!that.data.count) {
      wx.showToast({
        title: '请输入数量',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    }
    if (!that.data.address) {
      wx.showToast({
        title: '请输入用工地点',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    }
    if (!that.data.nums) {
      wx.showToast({
        title: '请输入需要人数',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    }
    if (!that.data.description) {
      wx.showToast({
        title: '请输入货物描述',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      });
      return false;
    }
    //开始时间判断
    var curTimes = new Date().getTime() / 1000;
    var openTimes = new Date(that.data.starttime.replace(/-/g, '/')).getTime() / 1000;
    var diff = curTimes + 3000;
    if (openTimes < diff) {
      wx.showToast({
        title: '请设置开工时间,开工时间必须大于当前时间至少1小时',
        icon: 'none',
        //image: '/jujiwuliu/resource/images/error.png',
        duration: 2000
      });
      return false;
    }

    if(!that.data.lat || !that.data.lng){
      wx.showToast({
        title: '请设置开工位置',
        icon: 'warn',
        image: '/jujiwuliu/resource/images/error.png',
        duration: 1000
      })
      return false
    }

    app.util.request({
      url: 'entry/wxapp/getrelease',
      data: {
        formid: formid,
        order_id: that.data.order_id,
        type: that.data.type,
        static: that.data.static,
        price: that.data.price,
        total_price: that.data.total_price,
        count_price: that.data.count_price,
        count: that.data.count,
        starttime: that.data.starttime,
        region: that.data.objectProvinces[that.data.provincesIndex].name + ' ' + that.data.objectCitys[that.data.citysIndex].name + ' ' + that.data.objectDistricts[that.data.districtsIndex].name,
        address: that.data.address,
        nums: that.data.nums,
        sex: that.data.sexIndex,
        description: that.data.description,
        images: that.data.images,
        appoint: that.data.check_userids,
        lat: that.data.lat,
        lng: that.data.lng,
        bond: that.data.bond,
        cargo: that.data.cargoIndex,
        time_uint: that.data.timeUintIndex
      },
      method: "POST",
      success: function (res) {//后台直接返回支付参数 节省请求 增加用户体验
        //缓存id
        if (res.data && res.data.data && !res.data.errno) {
          console.log(res);
          that.setData({
            order_id: res.data.data.id,
            pay_data: res.data.data.pay_data,
            //credit_enough: res.data.data.credit_enough,
            'payData.credit_enough': res.data.data.credit_enough,
            'payData.bond': res.data.data.all_money,
            //modalPayBtns:1
            'payData.modalPayBtns': 1
          })
        }

      },
      fail: function (res) {
        console.log(res)
        var message = res.data.message
        wx.showModal({
          title: '请求失败',
          content: message,
          showCancel: false
        });
        return false;
      }
    })
  },

  onSocketMessageCallback: function (msg) {
    var that = this
    console.log(msg)
    app.onSocketMessageCallback(msg);//重载后要执行一下全局方法
    msg = JSON.parse(msg);

    if (msg.type == 'location') {
      if (msg.nearby_address != '无') {

      }
    }

  },

  gotomap : function(){
    wx.navigateTo({
      url: '/jujiwuliu/pages/issuer/map/index?lat=' + this.data.latitude + "&lng=" + this.data.longitude,
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    app.webSocket.onSocketMessageCallback = that.onSocketMessageCallback;
    if (that.data.dataTmp != undefined && typeof (that.data.dataTmp) != 'object') {
      that.setData({
        dataTmp: that.data
      })
    }

    app.util.footer(that);
    //获取地理位置
    wx.getLocation({
      type: 'wgs84',
      success(res) {
        const latitude = res.latitude
        const longitude = res.longitude
        const speed = res.speed
        const accuracy = res.accuracy
        that.setData({
          latitude: latitude,
          longitude: longitude
        })
        //ajax获取地理位置（阻塞式的 比soket反馈好）
        app.util.request({
          'url': 'entry/wxapp/getlocation',
          'cachetime': '180',
          'data': { lat: latitude, lng: longitude },
          success: function (res) {
            console.log(res);
            that.setData({
              address: res.data.data.result.address_component.street
            });
          }
        });
      }, fail: function () {
        that.setData({
          locationModelShow: 1,
        });
      }
    })
    var obj = dateTimePicker.dateTimePicker(that.data.startYear, that.data.endYear);
    var lastArray = obj.dateTimeArray;
    var lastTime = obj.dateTime;
    var dateTime = obj.dateTime;

    that.setData({
      dateTime: obj.dateTime,
      dateTimeArray: obj.dateTimeArray,
      starttime: obj.dateTimeArray[0][dateTime[0]] + '-' + obj.dateTimeArray[1][dateTime[1]] + '-' + obj.dateTimeArray[2][dateTime[2]] + ' ' + obj.dateTimeArray[3][dateTime[3]] + ':' + obj.dateTimeArray[4][dateTime[4]] + ':' + obj.dateTimeArray[5][dateTime[5]],
    });
  },

  //  点击日期组件确定事件
  getDateTime: function (e) {
    console.log(e.detail.value)
    var obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    var dateTime = e.detail.value;
    this.setData({
      dateTime: e.detail.value,
      starttime: obj.dateTimeArray[0][dateTime[0]] + '-' + obj.dateTimeArray[1][dateTime[1]] + '-' + obj.dateTimeArray[2][dateTime[2]] + ' ' + obj.dateTimeArray[3][dateTime[3]] + ':' + obj.dateTimeArray[4][dateTime[4]] + ':' + obj.dateTimeArray[5][dateTime[5]],
    });
  },
  getDateTimeColumn: function (e) {
    var obj = dateTimePicker.dateTimePicker(this.data.startYear, this.data.endYear);
    var arr = this.data.dateTime,
      dateArr = this.data.dateTimeArray;
    arr[e.detail.column] = e.detail.value;
    dateArr[2] = dateTimePicker.getMonthDay(
      dateArr[0][arr[0]],
      dateArr[1][arr[1]]
    );

    this.setData({
      dateTimeArray: dateArr,
      dateTime: arr
    });
  },
  sexPickerChange: function (e) {
    this.setData({
      sexIndex: e.detail.value
    });
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  bindProvincesChange(e) {
    var that = this;
    that.setData({
      provincesIndex: e.detail.value
    })
    that.get_citys(that.data.objectProvinces[e.detail.value].id, 2, 0);//请求市

  },
  bindCitysChange(e) {
    var that = this;
    that.setData({
      citysIndex: e.detail.value
    })
    that.get_citys(that.data.objectCitys[e.detail.value].id, 3, 0);//请求区域
  },
  bindDistrictsChange(e) {
    var that = this;
    that.setData({
      districtsIndex: e.detail.value
    })
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;

    wx.getLocation({
      type: 'wgs84',
      success(res) {
        const latitude = res.latitude
        const longitude = res.longitude
        const speed = res.speed
        const accuracy = res.accuracy
        //ajax获取地理位置（阻塞式的 比soket反馈好）
        app.util.request({
          'url': 'entry/wxapp/getlocation',
          //   'cachetime': '180',//不设置缓存 实时读取
          'data': { lat: latitude, lng: longitude },
          success: function (res) {
            // if (res.data.data.nearby_address != '无') {
            //   that.setData({
            //     address: res.data.data.nearby_address
            //   });

            // }
            /*
            that.setData({
              address: res.data.data.result.address_component.street,
            });
            */
            //获取到位置之后再执行获取选项的代码
            if (that.data.get_address_show == 0) {
              that.setData({
                get_address_show: 1
              })
              app.util.request({
                'url': 'entry/wxapp/getchildren',
                cache: false,
                success: function (prores) {
                  var provinces = [];
                  var objectProvinces = [];
                  var provincesIndex = 0;
                  prores.data.data.result[0].forEach(function (pro, idx) {
                    provinces.push(pro.fullname);
                    objectProvinces[idx] = pro;
                    objectProvinces[idx].name = pro.fullname;
                    objectProvinces[idx].fullname = pro.name;
                    if (res.data.data.result) {
                      if (pro.fullname == res.data.data.result.address_component.province) {
                        provincesIndex = idx;
                      }
                    }
                  })
                  that.get_citys(provincesIndex ? prores.data.data.result[0][provincesIndex].id : prores.data.data.result[0][0].id, 2, res.data.data.result.address_component ? res.data.data.result.address_component : 0);//请求市区

                  that.setData({
                    provinces: provinces,
                    objectProvinces: prores.data.data.result[0],
                    provincesIndex: provincesIndex
                  })
                  console.log(prores.data.data.result[0]);
                }
              })
            }

          }
        });
      }, fail: function () {
        that.setData({
          locationModelShow: 1,
        });
      }
    })

    //获取系统设置 并且缓存3分钟 引导页
    app.util.request({
      'url': 'entry/wxapp/getsetting',
      'cachetime': '180',
      'data': that.data.location,
      success: function (res) {
        that.setData({
          setting_set: res.data.data
        })

        var sto_setting_set = wx.getStorageSync('setting_set')
        sto_setting_set = sto_setting_set ? sto_setting_set : {};
        sto_setting_set.data = res.data.data;
        wx.setStorageSync('setting_set', sto_setting_set)
        //设置第一个选项单价为默认值
        //console.log(sto_setting_set.data.dock_light)
        if(!that.data.price){
          that.setData({
            price: that.data.type == 1 ? sto_setting_set.data.temporary_light : sto_setting_set.data.dock_light
          })
        }

        //下面是页面初始化

        //获取历史工人
        app.util.request({
          'url': 'entry/wxapp/getAppoint',
          success: function (res) {
            that.setData({
              user_list: res.data.data ? res.data.data : []
            })
          },
          fail: function () {
            that.setData({
              user_list: []
            })
          }
        })


      }
    });


  },
  get_citys: function (id, type, address_component) {//id 传入id type 层级 address_component参考地址(默认选中地址)
    console.log(id);
    var that = this;
    app.util.request({
      'url': 'entry/wxapp/getchildren',
      data: {
        id: id
      },
      cache: false,
      success: function (prores) {
        console.log(prores);
        var areas = [];
        var objectareas = [];
        var areasIndex = 0;
        prores.data.data.result[0].forEach(function (pro, idx) {
          areas.push(pro.fullname);
          objectareas[idx] = pro;
          objectareas[idx].name = pro.fullname;
          objectareas[idx].fullname = pro.name;
          if (address_component != 0) {
            if (type == 2) {
              if (pro.fullname == address_component.city) {
                areasIndex = idx;
              }
            }
            if (type == 3) {
              if (pro.fullname == address_component.district) {
                areasIndex = idx;
              }
            }
          }

        })

        if (type == 2) {
          that.setData({
            citys: areas,
            objectCitys: prores.data.data.result[0],
            citysIndex: areasIndex
          })
          that.get_citys(areasIndex ? prores.data.data.result[0][areasIndex].id : prores.data.data.result[0][0].id, 3, address_component ? address_component : 0);//请求市区
        }
        if (type == 3) {
          that.setData({
            districts: areas,
            objectDistricts: prores.data.data.result[0],
            districtsIndex: areasIndex
          })
        }
      }
    })
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

  },


  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    //转发事件 带上用户id （加密） 
    var user_info = wx.getStorageSync('user_info');

    return {
      title: '巨吉搬运',
      path: '/jujiwuliu/pages/index/index?introducer=' + app.util.base64_encode(user_info.id),
    }
  }
})