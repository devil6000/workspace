var e = getApp();

Page({
  data: {
    lng: 120.81303,
    lat: 27.93252,
    scale: 13,
    realname: "刘",
    address: "温州市龙湾区蓝江软件园",
    mobile:15555555555,
    subkey:"MF2BZ-SUJCQ-ISK5A-GHYV5-LQU42-KPB3I",
    //subkey: "LNYBZ-JK5K4-XLOUZ-DFA76-53Y66-ZFFEL",
    markers: [{
      iconPath: "../../../resource/images/location.png",
      id: 0,
      longitude:0,
      latitude: 0,
        width: 30,
        height: 30,
        label: {
            content: "客户地址",
            color: "#666666",
            fontSize: 12,
            borderRadius: 10,
            bgColor: "#ffffff",
            padding: 5,
            display: "ALWAYS",
            textAlign: "center",
            x: -20,
            y: -60
        }
    } ],
  },
  get_list: function() {},
  regionchange(e) {
    console.log(e.type)
  },
  markertap(e) {
    console.log(e.markerId)
  },
  controltap(e) {
    console.log(e.controlId)
  },
  onLoad: function(t) {
    var that = this;
    e.util.request({
      url: 'entry/wxapp/getMapInfo',
      data: {id: t.id},
      method: 'POST',
      success: function(res){
        var data = res.data.data
        console.log(data)
        var markers = that.data.markers
        markers[0].longitude = parseFloat(data.lng)
        markers[0].latitude = parseFloat(data.lat)
        markers[0].label.content = data.address
        that.setData({
          lng: parseFloat(data.lng),
          lat: parseFloat(data.lat),
          realname: data.realname,
          address: data.address,
          mobile: data.mobile,
          markers: markers
        })
      }
    })
  },
    open_location: function (e) {
      var that = this;
      wx.getLocation({
        success: function(res) {
          wx.openLocation({
            latitude: parseFloat(that.data.lat),
            longitude: parseFloat(that.data.lng),
            name: that.data.address,
          })
        },
      })
    },
});