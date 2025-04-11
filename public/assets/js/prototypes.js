Array.prototype.removeObject=function(keyName,removeItem){
    let value=this;
    return $.grep(value, function(e){ 
        return e[keyName] != removeItem; 
    });
}
Array.prototype.sorting=function(sortBy){
    let value=this;
    return value.sort(function(a,b){
        var aName = a[sortBy].toLowerCase();
        var bName = b[sortBy].toLowerCase(); 
        return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
    });
}
Array.prototype.sorting2=function(Key1,Key2){
    let value=this;
    return value.sort(function(a,b){
        var aName = a[Key1].toLowerCase()+" "+ a[Key2].toLowerCase();
        var bName = b[Key1].toLowerCase()+" "+ b[Key2].toLowerCase(); 
        return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
    });
}
Array.prototype.objectExists=function(keyName,Value){
    let value=this;
    for(item of value){
        if(item[keyName]==Value) return true;
    }
    return false;
}
Array.prototype.getObjectByValue=function(keyName,Value){
    let value=this;
    for(item of value){
        if(item[keyName]==Value) return item;
    }
    return {};
}
Array.prototype.arrayFilter=function(arrayTwo,KeyName){
    let arrayOne=this;
    let _return=[];
    for(item1 of arrayTwo){
        let status=arrayOne.objectExists(KeyName,item1[KeyName]);
        if(status==false){_return[_return.length]=item1;}
    }
    return _return;
}
Number.prototype.minutes=function(){
    let _thisVal=this.valueOf();
    
    let MINS_PER_YEAR = 24 * 365 * 60
    let MINS_PER_MONTH = 24 * 30 * 60
    let MINS_PER_DAY = 24 * 60
    let MIN_PER_HOUR= 60;

    let minutes=_thisVal;
    let year = Math.floor(minutes / MINS_PER_YEAR);
    minutes = minutes - (year * MINS_PER_YEAR);

    let months = Math.floor(minutes / MINS_PER_MONTH);
    minutes = minutes - (months * MINS_PER_MONTH);

    let days = Math.floor(minutes / MINS_PER_DAY);
    minutes = minutes - (days * MINS_PER_DAY);

    let hours = Math.floor(minutes / MIN_PER_HOUR);
    minutes = minutes - (hours * MIN_PER_HOUR);

    let convert={
        toHours:function(){
            return {
                hour:hours,
                minutes:minutes
            }
        },
        toDays:function(){
            return {
                days:days,
                hours:hours,
                minutes:minutes
            }
        },
        toMonth:function(){
            return {
                months:months,
                days:days,
                hours:hours,
                minutes:minutes
            }
        },
        toYear:function(){
            return {
                year:year,
                months:months,
                days:days,
                hours:hours,
                minutes:minutes
            }
        }
    }
    return convert;
}
Number.prototype.toMinutes=function(from="hours"){
    let _this=this;
    let value=this.valueOf();
    if((value==undefined)||(value=="")){
        value=0;
    }
    let MINS_PER_YEAR = 24 * 365 * 60
    let MINS_PER_MONTH = 24 * 30 * 60
    let MINS_PER_DAY = 24 * 60
    let MIN_PER_HOUR= 60;

    from=from.toString().toLowerCase();
    value=value.toString().split(".");
    let value1=parseInt(value[0]);
    let decimal=0; 
    if(value.length>1){ 
        decimal=parseInt(value[1]);
    }
    if(from=="year"){
        $return=Math.floor(value1 * MINS_PER_YEAR);
        return $return;
    }else if(from=="month"){
        $return=Math.floor(value1 * MINS_PER_MONTH);
        return $return;
    }else if(from=="month"){
        $return=Math.floor(value1 * MINS_PER_DAY);
        return $return;
    }else {
        $return=Math.floor(value1 * MIN_PER_HOUR);
        $return+=decimal;
        return $return;
    }
    return value;
}
String.prototype.slugify=function(){
    let value=this;
    return value
    .toString()
    .trim()
    .toLowerCase()
    .replace(/\s+/g, "-")
    .replace(/[^\w\-]+/g, "")
    .replace(/\-\-+/g, "-")
    .replace(/^-+/, "")
    .replace(/-+$/, "");
}
String.prototype.lpad = function(padString, length) {
    var str = this;
    while (str.length < length)
        str = padString + str;
    return str;
}
String.prototype.isValidGSTNumber=function(){
    let value=this;
    return /\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}[Z]{1}[A-Z\d]{1}/.test(value);
}
String.prototype.isValidPANNumber=function(){
    let value=this;
    return /[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(value);
}
String.prototype.isValidEMail=function(){
    let value=this;
    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
    return emailReg.test( value );
}
String.prototype.toCustomFormat = function(format) {
    let date=new Date(this.valueOf());
    let monthNames =["Jan","Feb","Mar","Apr",
                      "May","Jun","Jul","Aug",
                      "Sep", "Oct","Nov","Dec"];
    let day = date.getDate().toString().lpad("0",2);
    let monthIndex = date.getMonth();
    let MonthNumber=(monthIndex+1).toString().lpad("0",2);
    let monthName = monthNames[monthIndex];
    let fullYear = date.getFullYear();
    let Year = date.getFullYear().toString().substr(-2);


    let hr = date.getHours();
    let min = date.getMinutes();
    let sec = date.getSeconds();
    let hr1=hr;
    let AMPMCase=format.toString().trim().substr(-1);
    let AmPm="am";
    if(hr>=12){
        AmPm="pm";
    }
    if(AMPMCase=="A"){
        AmPm=AmPm.toString().toUpperCase();
    }
    if(hr>12){
        hr1-=12;
    }
    hr=hr.toString().lpad("0",2);
    hr1=hr1.toString().lpad("0",2);
    min=min.toString().lpad("0",2);
    sec=sec.toString().lpad("0",2);
    if(format=="d/M/Y"){
        return `${day}/${monthName}/${fullYear}`;
    }else if(format=="d-M-Y"){
        return `${day}-${monthName}-${fullYear}`;
    }else if(format=="d-m-Y"){
        return `${day}-${MonthNumber}-${fullYear}`;
    }else if(format=="d/m/Y"){
        return `${day}/${MonthNumber}/${fullYear}`;
    }else if(format=="d-m-y"){
        return `${day}-${MonthNumber}-${Year}`;
    }else if(format=="d/m/y"){
        return `${day}/${MonthNumber}/${Year}`;
    }else if(format=="Y-M-d"){
        return `${fullYear}-${monthName}-${day}`;
    }else if(format=="Y/M/d"){
        return `${fullYear}/${monthName}/${day}`;
    }else if(format=="Y-m-d"){
        return `${fullYear}-${MonthNumber}-${day}`;
    }else if(format=="Y/m/d"){
        return `${fullYear}/${MonthNumber}/${day}`;
    }else if(format=="y-m-d"){
        return `${Year}-${MonthNumber}-${day}`;
    }else if(format=="y/m/d"){
        return `${Year}/${MonthNumber}/${day}`;
    }else if(format=="M d,Y"){
        return `${monthName} ${day}, ${fullYear}`;
    }else if(format=="h:i:s A"){
        return `${hr1}:${min}:${sec} ${AmPm}`;
    }else if(format=="h:i:s a"){
        return `${hr1}:${min}:${sec} ${AmPm}`;
    }else if(format=="H:i:s"){
        return `${hr}:${min}:${sec}`;
    }else{
        return `${day}-${monthName}-${fullYear} ${hr}:${min}:${sec}`;
    }
}
Date.prototype.toCustomFormat = function(format) {
    let monthNames =["Jan","Feb","Mar","Apr",
                      "May","Jun","Jul","Aug",
                      "Sep", "Oct","Nov","Dec"];
    let day = this.getDate().toString().lpad("0",2);
    let monthIndex = this.getMonth();
    let MonthNumber=(monthIndex+1).toString().lpad("0",2);
    let monthName = monthNames[monthIndex];
    let fullYear = this.getFullYear();
    let Year = this.getFullYear().toString().substr(-2);


    let hr = this.getHours();
    let min = this.getMinutes();
    let sec = this.getSeconds();
    let hr1=hr;
    let AMPMCase=format.toString().trim().substr(-1);
    let AmPm="am";
    if(hr>=12){
        AmPm="pm";
    }
    if(AMPMCase=="A"){
        AmPm=AmPm.toString().toUpperCase();
    }
    if(hr>12){
        hr1-=12;
    }
    hr=hr.toString().lpad("0",2);
    hr1=hr1.toString().lpad("0",2);
    min=min.toString().lpad("0",2);
    sec=sec.toString().lpad("0",2);
    if(format=="d/M/Y"){
        return `${day}/${monthName}/${fullYear}`;
    }else if(format=="d-M-Y"){
        return `${day}-${monthName}-${fullYear}`;
    }else if(format=="d-m-Y"){
        return `${day}-${MonthNumber}-${fullYear}`;
    }else if(format=="d/m/Y"){
        return `${day}/${MonthNumber}/${fullYear}`;
    }else if(format=="d-m-y"){
        return `${day}-${MonthNumber}-${Year}`;
    }else if(format=="d/m/y"){
        return `${day}/${MonthNumber}/${Year}`;
    }else if(format=="Y-M-d"){
        return `${fullYear}-${monthName}-${day}`;
    }else if(format=="Y/M/d"){
        return `${fullYear}/${monthName}/${day}`;
    }else if(format=="Y-m-d"){
        return `${fullYear}-${MonthNumber}-${day}`;
    }else if(format=="Y/m/d"){
        return `${fullYear}/${MonthNumber}/${day}`;
    }else if(format=="y-m-d"){
        return `${Year}-${MonthNumber}-${day}`;
    }else if(format=="y/m/d"){
        return `${Year}/${MonthNumber}/${day}`;
    }else if(format=="M d,Y"){
        return `${monthName} ${day}, ${fullYear}`;
    }else if(format=="h:i:s A"){
        return `${hr1}:${min}:${sec} ${AmPm}`;
    }else if(format=="h:i:s a"){
        return `${hr1}:${min}:${sec} ${AmPm}`;
    }else if(format=="H:i:s"){
        return `${hr}:${min}:${sec}`;
    }else if(format=="H:i"){
        return `${hr}:${min}`;
    }else if(format=="Ymd"){
        return `${fullYear}${MonthNumber}${day}`;
    }else if(format=="YMd"){
        return `${fullYear}${monthName}${day}`;
    }else if(format=="YmdHis"){
        return `${fullYear}${MonthNumber}${hr}${min}${sec}`;
    }else{
        return `${day}-${monthName}-${fullYear} ${hr}:${min}:${sec}`;
    }
}
Date.prototype.getDifference=function(date2){
    let _this=this;
    let minutes=((date2.getTime() - _this.getTime()) / 1000)/60;
        minutes= Math.abs(Math.round(minutes));
    let difference={
        inMinutes:function(){
            return minutes;
        },
        inHours:function(){
            let diff = minutes.minutes().toHours();
            return diff;
        },
        inDays:function(){
            let t2 = date2.getTime();
            let t1 = _this.getTime();
     
            return Math.floor((t2-t1)/(24*3600*1000));
        },
        inWeeks:function(){
            let t2 = date2.getTime();
            let t1 = _this.getTime();
            return parseInt((t2-t1)/(24*3600*1000*7));
        },
        inMonths:function(){
            let d1Y = _this.getFullYear();
            let d2Y = date2.getFullYear();
            let d1M = _this.getMonth();
            let d2M = date2.getMonth();
    
            return (d2M+12*d2Y)-(d1M+12*d1Y);
        },
        inYear:function(){
            return date2.getFullYear()-_this.getFullYear();
        },
        inDifference:function(){
            let diffMs = (date2 - _this); 
            let diffDays = Math.floor(diffMs / 86400000); // days
            let diffHrs = Math.floor((diffMs % 86400000) / 3600000); // hours
            let diffMins = Math.round(((diffMs % 86400000) % 3600000) / 60000); // minutes
            return {
                days:diffDays,
                hours:diffHrs,
                minutes:diffMins
            }
        }

    }
    return difference;
}
Date.prototype.toAdd=function(duration=0,interval="minutes"){
    let value=this;
    interval=interval.toString().toLowerCase();
    if(interval=="days"){
        value.setDate(value.getDate()+duration);
    }else if(interval=="years"){
        value.setFullYear(value.getFullYear()+duration);
    }else if(interval=="months"){
        value.setMonth(value.getMonth()+duration);
    }else if(interval=="minutes"){
        value.setMinutes(value.getMinutes()+duration);
    }else if(interval=="seconds"){
        value.setSeconds(value.getSeconds()+duration);
    }
    return value;
}