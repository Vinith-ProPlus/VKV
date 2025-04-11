(function ($) {
    $.fn.pplDataTable = function (options) {
        const root=$(this);
        var tblView=null;
        options=$.extend({
            processing:true,
            serverSide:false,
            pageLength:10,
            responsive:true,
            drawCallback:async(settings)=>{},
            createdRow: async (row, data, dataIndex)=> {},
            footerCallback: async (tr, data, start, end, display)=> {},
            formatNumber: async (toFormat) =>{},
            headerCallback: async (thead, data, start, end, display)=> {},
            infoCallback: undefined,
            initComplete: async (settings, json)=> {},
            preDrawCallback: async (settings)=> {},
            rowCallback: async (row, data)=> {},
            buttonColorClass:"btn-outline-dark",
            buttons:[
                'pageLength','copy','csv','excel','pdf','print','settings'
            ],
            lengthMenu: [[10, 25, 50,100,250,500, -1], [10, 25, 50,100,250,500, "All"]],
            ajax:"",
            columns:[],
            
            permissions:{
                add:0,
                view:0,
                edit:0,
                delete:0,
                copy:0,
                excel:0,
                csv:0,
                print:0,
                pdf:0,
                restore:0,
                showpwd:0
            },
            tableName:"",
        },options);
        const UI={
            input:(attr = {})=> {
                return createElement('input', attr);
            },
            div:(attr = {})=>  {
                return createElement('div', attr);
            },
            span:(attr = {})=> {
                return createElement('span', attr);
            },
            ul:(attr = {})=> {
                return createElement('ul', attr);
            },
            
            li:(attr = {})=> {
                return createElement('li', attr);
            },
            img:(attr = {})=> {
                return createElement('img', attr);
            },
            h1:(attr = {})=> {
                return createElement('h1', attr);
            },
            h2:(attr = {})=> {
                return createElement('h2', attr);
            },
            h3:(attr = {})=> {
                return createElement('h3', attr);
            },
            h4:(attr = {})=> {
                return createElement('h4', attr);
            },
            h5:(attr = {})=> {
                return createElement('h5', attr);
            },
            
            h6:(attr = {})=> {
                return createElement('h6', attr);
            },
            
            p:(attr = {})=> {
                return createElement('p', attr);
            },
            
            button:(attr = {})=> {
                return createElement('button', attr);
            },
            
            table:(attr = {})=> {
                return createElement('table', attr);
            },
            
            thead:(attr = {})=> {
                return createElement('thead', attr);
            },
            
            tbody:(attr = {})=> {
                return createElement('tbody', attr);
            },
            
            tfoot:(attr = {})=> {
                return createElement('tfoot', attr);
            },
            
            tr:(attr = {})=> {
                return createElement('tr', attr);
            },
            
            th:(attr = {})=> {
                return createElement('th', attr);
            },
            
            td:(attr = {})=> {
                return createElement('td', attr);
            },
            
            select:(attr = {})=>{
                return createElement('select', attr);
            },
            
            selectOption:(attr = {})=>{
                return createElement('option', attr);
            },
            a:(attr = {})=>{
                return createElement('a', attr);
            },
            
            label:(attr = {})=>{
                return createElement('label', attr);
            },
            option:(attr = {})=>{
                return createElement('option', attr);
            },
            link:(attr = {})=>{
                return createElement('link', attr);
            },
            script:async(attr = {})=>{
                return createElement('script', attr);
            },
            checkBox:async(attr = {})=>{
                return createElement('checkBox', attr);
            }
        }
        const createElement=(tagName, attr)=> {
            let el = document.createElement(tagName);
            for (let k in attr) {
                if (attr.hasOwnProperty(k)) {
                    el.setAttribute(k, attr[k]);
                }
            }
            return el;
        }
        var tableConfig={
            "tableSize":"",
            "background":"",
            "sortingBy":0,
            "sortingOrder":"desc",
            "height":"",
            "fixedColumns":{"left":0,"right":0},
            "isFixedHeading":false,
            "isFixedColumns":false,
            "isSearchEnabled":false,
            "isPageInfoEnabled":false,
            "isPaginationEnabled":true,
            "tableID":"",
            "header":[]
        }
        var tmpTableConfig={};
        var tblDTTable=null;
        const getTableConfig=async()=>{
            return await new Promise(async(resolve,reject)=>{
                $.ajax({
                    async:true,
                    type:"post",
                    url:options.tableConfig.get,
                    data:{
                        tableName:options.tableName
                    },
                    headers: { 'X-CSRF-Token' : options.csrfToken },
                    error:async(e, x, settings, exception)=>{reject()},
                    success:async(response)=>{
                        let tmpConfig=[];
                        if(response.status){
                            tmpConfig=response.config;
                        }
                        await checkJsonKeys(tmpConfig);
                        resolve([]);
                    }
                })
            });
        }
        const saveTableConfig=async()=>{
            $.ajax({
                async:true,
                type:"post",
                url:options.tableConfig.save,
                headers: { 'X-CSRF-Token' : options.csrfToken },
                data:{config:JSON.stringify(tableConfig),tableName:options.tableName},
                error:function(e, x, settings, exception){reject()},
                success:function(response){
                    if(response)bootstrap.Modal.getInstance(document.querySelector('.modal.show')).hide();
                }
            })
        }
        const generateTableUI=async()=>{
            try {
                tableConfig.tableID=(tableConfig.tableID!=""&& tableConfig.tableID!=undefined)?generateUUID():tableConfig.tableID;
                tblView=UI.table({class:"table "+tableConfig.tableSize,id:tableConfig.tableID});
                root.append(tblView);
    
                let thead=UI.thead()
                tblView.appendChild(thead);
    
                let tbody=UI.tbody()
                tblView.appendChild(tbody);
    
                const generateHeading=async()=>{
                    try {
                        if(tableConfig.header!=undefined){
                            for(let row of tableConfig.header){
                                let tr=UI.tr({})
                                thead.appendChild(tr);
                                for(let thItem of row){
                                    let attr=thItem.attr!=undefined?thItem.attr:[];
                                    let th=UI.th(attr)
                                    th.classList.add('text-'+thItem.align); 
                                    th.innerHTML=thItem.headingName
                                    tr.appendChild(th);
                                }
                            }
                        }
                    } catch (error) {
                        console.log(error);
                    }
                }
                const generateFooter=async()=>{
                    try {
                        if(tableConfig.footer!=undefined){
                            if(tableConfig.footer.length>0){
                                let tfoot=UI.tfoot()
                                tblView.appendChild(tfoot);
                                for(let row of tableConfig.footer){
                                    let tr=UI.tr({})
                                    tfoot.appendChild(tr);
                                    for(let thItem of row){
                                        let attr=thItem.attr!=undefined?thItem.attr:[];
                                            
                                        let th=UI.th(attr)
                                        th.classList.add('text-'+thItem.align); 
                                        th.innerHTML=thItem.headingName
                                        tr.appendChild(th);
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.log(error);
                    }
                }
                generateHeading();
                generateFooter();
            } catch (error) {
                console.log(error);
            }
        }
        const generateUUID=()=> {
            var d = new Date().getTime();
            var uuid = 'ppl-xxxxxxxxxxx-xx'.replace(/[xy]/g, function(c) {
                var r = (d + Math.random()*16)%16 | 0;
                d = Math.floor(d/16);
                return (c=='x' ? r : (r&0x3|0x8)).toString(16);
            });
            return uuid;
        }
        
        const lpad = async(str,padString, length)=> {
            while (str.length < length)
                str = padString + str;
            return str;
        }
        const dateFormat = async(format,date=null)=> {
            date=date==null?new Date():date;
            let monthNames =["Jan","Feb","Mar","Apr",
                            "May","Jun","Jul","Aug",
                            "Sep", "Oct","Nov","Dec"];
            let day = await lpad(date.getDate().toString(),"0",2);
            let monthIndex = date.getMonth();
            let MonthNumber=await lpad((monthIndex+1).toString(),"0",2);
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
            hr=lpad(hr.toString(),"0",2);
            hr1=lpad(hr1.toString(),"0",2);
            min=lpad(min.toString(),"0",2);
            sec=lpad(sec.toString(),"0",2);
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
        const checkJsonKeys=async(jsonData)=>{
            let tmpConfig=$.extend({
                "tableSize": "table-sm",
                "background": "none",
                "sortingBy": 1,
                "sortingOrder": "asc",
                "height": "400",
                "fixedColumns": {
                    "left": 0,
                    "right": 0
                },
                "isFixedHeading": "Disabled",
                "isFixedColumns": "Disabled",
                "isSearchEnabled": "Enabled",
                "isPageInfoEnabled": "Enabled",
                "isPaginationEnabled": "Enabled",
                "isSearchBuilderEnabled": "Disabled",
                "searchBuilderText":"Custom Filter",
                "searchBuilderLabel":"",
                "defaultPageLength": "10",
                "pageLength": "10",
            },jsonData);
            tmpConfig.footer=tmpConfig.footer!=undefined?tmpConfig.footer:[];
            tmpConfig.header=tmpConfig.header!=undefined?tmpConfig.header:[];
            $.each( tmpConfig.header, function( rowIndex, columns ) {
                $.each( columns, function( columnIndex, column ) {
                    tmpConfig.header[rowIndex][columnIndex]=$.extend({
                        "columnName": "Column "+columnIndex,
                        "headingName": "Column "+columnIndex,
                        "display": "Show",
                        "align": "left",
                        "searchBuilder":{ "status":"Disabled", "type": "string" },
                        "attr": {},
                        "others": {
                            "type": "",
                            "format": ""
                        }
                    },column);

                })
            })
            tableConfig=tmpConfig;
            return tmpConfig;
        }
        const generateSettingsUI=async()=>{
            tmpTableConfig=tableConfig;
            const modalID=generateUUID();
            const body=document.querySelector('body');
            let modal=UI.div({class:"modal fade dt-customize-options", id:modalID, "data-bs-backdrop":"static", "data-bs-keyboard":"false", "tabindex":"-1"});
            body.appendChild(modal);

            let modalDialog=UI.div({class:"modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down modal-dialog-scrollable"});
            modal.appendChild(modalDialog);

            let modalContent=UI.div({class:"modal-content"})
            modalDialog.appendChild(modalContent);

            const modalHeaderUI=async()=>{
                let modalHeader=UI.div({class:"modal-header"});
                modalContent.appendChild(modalHeader);

                let modalTitle=UI.h5({class:"modal-title"});
                modalTitle.innerHTML="Table Settings";
                modalHeader.appendChild(modalTitle);

                let closeBtn=UI.button({type:"button", class:"btn-close", "data-bs-dismiss":"modal", "aria-label":"Close"});
                modalHeader.appendChild(closeBtn);
            }
            const modalBodyUI=async()=>{
                let modalBody=UI.div({class:"modal-body"});
                modalContent.appendChild(modalBody);
                const tabContent=async(col)=>{
                    let tabContent=UI.div({class:"tab-content", id:modalID+"-tabContent"});
                    col.appendChild(tabContent);
                    const generalTabUI=async()=>{
                        let generalTab=UI.div({class:"tab-pane fade show active", id:modalID+"-general", role:"tabpanel", "aria-labelledby":"general-home-tab"})
                        tabContent.appendChild(generalTab);

                        const FixedHeaderUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Fixed Header";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"})
                            col.appendChild(formGroup);

                            let inputGroup=UI.div({class:"input-group"});
                            formGroup.appendChild(inputGroup);

                            let inputGroupText=UI.div({class:"input-group-text"});
                            inputGroup.appendChild(inputGroupText);

                            let checkBox=UI.input({class:"form-check-input mt-0", type:"checkbox", value:""})
                            inputGroupText.appendChild(checkBox);
                            

                            let input=UI.input({type:"number", class:"form-control", id:"txtHeight","disabled":"true",value:tmpTableConfig.height});
                            inputGroup.appendChild(input);
                            input.addEventListener('keyup',function(){
                                tmpTableConfig.height=input.value;
                            });

                            let inputGroupText1=UI.span({class:"input-group-text"});
                            inputGroupText1.innerHTML="px"
                            inputGroup.appendChild(inputGroupText1);

                            $(checkBox).on('click',function(){
                                if($(checkBox).prop('checked')){
                                    tmpTableConfig.isFixedHeading="Enabled";
                                    $(input).removeAttr('disabled');
                                }else{
                                    tmpTableConfig.isFixedHeading="Disabled";
                                    $(input).attr('disabled','disabled');
                                }
                            });
                            if(tmpTableConfig.isFixedHeading=="Enabled"){
                                $(checkBox).trigger('click');
                            }
                        }
                        const FixedColumnsUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Fixed Columns";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"})
                            col.appendChild(formGroup);

                            let inputGroup=UI.div({class:"input-group"});
                            formGroup.appendChild(inputGroup);

                            let inputGroupText=UI.div({class:"input-group-text"});
                            inputGroup.appendChild(inputGroupText);

                            let checkBox=UI.input({class:"form-check-input mt-0", type:"checkbox", value:""})
                            inputGroupText.appendChild(checkBox);

                            let leftInputGroupText=UI.span({class:"input-group-text"});
                            leftInputGroupText.innerHTML="Left"
                            inputGroup.appendChild(leftInputGroupText);

                            let fixedLeftColumns=UI.input({type:"number", class:"form-control", step:1, id:"txtFixedLeftColumns", value:tmpTableConfig.fixedColumns.left, "disabled":true});
                            inputGroup.appendChild(fixedLeftColumns);
                            fixedLeftColumns.addEventListener('keyup',function(){
                                tmpTableConfig.fixedColumns.left=fixedLeftColumns.value;
                            });

                            let rightInputGroupText=UI.span({class:"input-group-text"});
                            rightInputGroupText.innerHTML="Right"
                            inputGroup.appendChild(rightInputGroupText);

                            let fixedRightColumns=UI.input({type:"number", class:"form-control", step:1, id:"txtFixedRightColumns", value:tmpTableConfig.fixedColumns.right, "disabled":true});
                            inputGroup.appendChild(fixedRightColumns);
                            fixedRightColumns.addEventListener('keyup',function(){
                                tmpTableConfig.fixedColumns.right=fixedRightColumns.value;
                            });
                            
                            $(checkBox).on('click',function(){
                                if($(checkBox).prop('checked')){
                                    tmpTableConfig.isFixedColumns="Enabled";
                                    $(fixedLeftColumns).removeAttr('disabled');
                                    $(fixedRightColumns).removeAttr('disabled');
                                }else{
                                    tmpTableConfig.isFixedColumns="Disabled";
                                    $(fixedLeftColumns).attr('disabled','disabled');
                                    $(fixedRightColumns).attr('disabled','disabled');
                                }
                            });
                            if(tmpTableConfig.isFixedColumns=="Enabled"){
                                $(checkBox).trigger('click');
                            }
                        }
                        const SearchUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Search";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let select=UI.select({class:"form-control"});
                            formGroup.appendChild(select);

                            let optEnabled=UI.option({value:"Enabled"});
                            optEnabled.innerHTML="Enabled";
                            select.appendChild(optEnabled);
                            if(tmpTableConfig.isSearchEnabled=="Enabled"){optEnabled.setAttribute('selected','selected');}

                            let optDisabled=UI.option({value:"Disabled"});
                            optDisabled.innerHTML="Disabled";
                            select.appendChild(optDisabled);
                            if(tmpTableConfig.isSearchEnabled=="Disabled"){optDisabled.setAttribute('selected','selected');}

                            select.addEventListener('change',function(){ 
                                tmpTableConfig.isSearchEnabled=select.value;
                            });
                        }
                        const SearchBuilderUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Custom Search";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let select=UI.select({class:"form-control"});
                            formGroup.appendChild(select);

                            let optEnabled=UI.option({value:"Enabled"});
                            optEnabled.innerHTML="Enabled";
                            select.appendChild(optEnabled);
                            if(tmpTableConfig.isSearchBuilderEnabled=="Enabled"){optEnabled.setAttribute('selected','selected');}

                            let optDisabled=UI.option({value:"Disabled"});
                            optDisabled.innerHTML="Disabled";
                            select.appendChild(optDisabled);
                            if(tmpTableConfig.isSearchBuilderEnabled=="Disabled"){optDisabled.setAttribute('selected','selected');}

                            select.addEventListener('change',function(){ 
                                tmpTableConfig.isSearchBuilderEnabled=select.value;
                                if(select.value=="Disabled"){
                                    $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").attr('disabled','disabled');
                                    $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").val('Disabled').trigger('change');
                                }else{
                                    $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").removeAttr('disabled');
                                    $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").val('Enabled').trigger('change');
                                }
                                
                            });
                        }
                        const PageInfoUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Page Info";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let selectPageInfo=UI.select({class:"form-control"});
                            formGroup.appendChild(selectPageInfo);

                            let optEnabled=UI.option({value:"Enabled"});
                            optEnabled.innerHTML="Enabled";
                            selectPageInfo.appendChild(optEnabled);
                            if(tmpTableConfig.isPageInfoEnabled=="Enabled"){optEnabled.setAttribute('selected','selected');}

                            let optDisabled=UI.option({value:"Disabled"});
                            optDisabled.innerHTML="Disabled";
                            selectPageInfo.appendChild(optDisabled);
                            if(tmpTableConfig.isPageInfoEnabled=="Disabled"){optDisabled.setAttribute('selected','selected');}

                            selectPageInfo.addEventListener('change',function(){
                                tmpTableConfig.isPageInfoEnabled=selectPageInfo.value;
                            });
                        }
                        const PaginationUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Pagination";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let inputGroup=UI.div({class:"input-group"});
                            formGroup.appendChild(inputGroup);

                            let select=UI.select({class:"form-control"});
                            inputGroup.appendChild(select);

                            let optEnabled=UI.option({value:"Enabled"});
                            optEnabled.innerHTML="Enabled";
                            select.appendChild(optEnabled);
                            if(tmpTableConfig.isPaginationEnabled=="Enabled"){optEnabled.setAttribute('selected','selected');}

                            let optDisabled=UI.option({value:"Disabled"});
                            optDisabled.innerHTML="Disabled";
                            select.appendChild(optDisabled);
                            if(tmpTableConfig.isPaginationEnabled=="Disabled"){optDisabled.setAttribute('selected','selected');}

                            //page Length
                            let pageLengthSelect=UI.select({class:"form-control","disabled":"disabled"});
                            inputGroup.appendChild(pageLengthSelect);
                            
                            let t=options.lengthMenu;
                            for(let i=0;i<t[0].length;i++){
                                
                                let optPageLength=UI.option({value:t[0][i]});
                                optPageLength.innerHTML=t[1][i]==undefined?"":t[1][i];
                                pageLengthSelect.appendChild(optPageLength);
                                if(tmpTableConfig.pageLength==t[0][i]){optPageLength.setAttribute('selected','selected');}
                            }

                            pageLengthSelect.addEventListener('change',function(){
                                tmpTableConfig.pageLength=pageLengthSelect.value;
                            });
                            select.addEventListener('change',function(){applyChanges();});
                            const applyChanges=async()=>{
                                
                                tmpTableConfig.isPaginationEnabled=select.value;
                                if(select.value=="Disabled"){
                                    tmpTableConfig.pageLength="-1"
                                    $(pageLengthSelect).attr('disabled','disabled');
                                }else{
                                    tmpTableConfig.pageLength=tmpTableConfig.defaultPageLength;
                                    $(pageLengthSelect).removeAttr('disabled');
                                }
                                $(pageLengthSelect).val(tmpTableConfig.pageLength).trigger("change");
                            }
                            applyChanges();
                            
                        }
                        const TableSizeUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Table Size";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let select=UI.select({class:"form-control"});
                            formGroup.appendChild(select);

                            let optSmall=UI.option({value:"table-sm"});
                            optSmall.innerHTML="Small";
                            select.appendChild(optSmall);
                            if(tmpTableConfig.tableSize=="table-sm"){optSmall.setAttribute('selected','selected');}
                            
                            let optNormal=UI.option({value:""});
                            optNormal.innerHTML="Default";
                            select.appendChild(optNormal);
                            if(tmpTableConfig.tableSize==""){optNormal.setAttribute('selected','selected');}

                            let optLarge=UI.option({value:"table-lg"});
                            optLarge.innerHTML="Large";
                            select.appendChild(optLarge);
                            if(tmpTableConfig.tableSize=="table-lg"){optLarge.setAttribute('selected','selected');}

                            select.addEventListener('change',function(){
                                tmpTableConfig.tableSize=select.value;
                            });
                        }
                        const HeadingBackgroundUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Heading Background";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let select=UI.select({class:"form-control"});
                            formGroup.appendChild(select);

                            let optNone=UI.option({value:""});
                            optNone.innerHTML="None";
                            select.appendChild(optNone);
                            if(tmpTableConfig.background==""){optNone.setAttribute('selected','selected');}

                            let optPrimary=UI.option({value:"table-primary"});
                            optPrimary.innerHTML="Primary";
                            select.appendChild(optPrimary);
                            if(tmpTableConfig.background=="table-primary"){optPrimary.setAttribute('selected','selected');}

                            let optSecondary=UI.option({value:"table-secondary"});
                            optSecondary.innerHTML="Secondary";
                            select.appendChild(optSecondary);
                            if(tmpTableConfig.background=="table-secondary"){optSecondary.setAttribute('selected','selected');}

                            let optSuccess=UI.option({value:"table-success"});
                            optSuccess.innerHTML="Success";
                            select.appendChild(optSuccess);
                            if(tmpTableConfig.background=="table-success"){optSuccess.setAttribute('selected','selected');}

                            let optDanger=UI.option({value:"table-danger"});
                            optDanger.innerHTML="Danger";
                            select.appendChild(optDanger);
                            if(tmpTableConfig.background=="table-danger"){optDanger.setAttribute('selected','selected');}

                            let optWarning=UI.option({value:"table-warning"});
                            optWarning.innerHTML="Warning";
                            select.appendChild(optWarning);
                            if(tmpTableConfig.background=="table-warning"){optWarning.setAttribute('selected','selected');}

                            let optInfo=UI.option({value:"table-info"});
                            optInfo.innerHTML="Info";
                            select.appendChild(optInfo);
                            if(tmpTableConfig.background=="table-info"){optInfo.setAttribute('selected','selected');}

                            let optLight=UI.option({value:"table-light"});
                            optLight.innerHTML="Light";
                            select.appendChild(optLight);
                            if(tmpTableConfig.background=="table-light"){optLight.setAttribute('selected','selected');}

                            let optDark=UI.option({value:"table-dark"});
                            optDark.innerHTML="Dark";
                            select.appendChild(optDark);
                            if(tmpTableConfig.background=="table-dark"){optDark.setAttribute('selected','selected');}

                            select.addEventListener('change',function(){
                                tmpTableConfig.background=select.value;
                            });
                        }
                        const SortingUI=async()=>{
                            let row = UI.div({class:"row justify-content-center mt-20"})
                            generalTab.appendChild(row);

                            let labelCol=UI.div({class:"col-6 col-lg-3 align-items-center"});
                            row.appendChild(labelCol);

                            let label=UI.label({});
                            label.innerHTML="Sorting";
                            labelCol.appendChild(label);

                            let col=UI.div({class:"col-6 col-lg-4"});
                            row.appendChild(col);

                            let formGroup=UI.div({class:"form-group"});
                            col.appendChild(formGroup);
                            
                            let inputgroup=UI.div({class:"input-group"});
                            formGroup.appendChild(inputgroup);

                            //sorting by
                            let selectSortingBy=UI.select({class:"form-control lstSortingBy"});
                            inputgroup.appendChild(selectSortingBy);
                            
                            for(let row of tmpTableConfig.header){
                                let i=0;
                                for(let col of row){
                                    if(col.headingName.toLowerCase()!=="action"){
                                        let sortingByOpt=UI.option({value:i});
                                        sortingByOpt.innerHTML=col.headingName;
                                        selectSortingBy.appendChild(sortingByOpt);
                                        if(tmpTableConfig.sortingBy==i){sortingByOpt.setAttribute('selected','selected');}
                                    }
                                    i++;
                                }
                            }

                            //sorting order
                            let selectSortingOrder=UI.select({class:"form-control lstSortingOrder"});
                            inputgroup.appendChild(selectSortingOrder);

                            let optAscending=UI.option({value:"asc"});
                            optAscending.innerHTML="Ascending";
                            selectSortingOrder.appendChild(optAscending);
                            if(tmpTableConfig.sortingOrder=="asc"){optAscending.setAttribute('selected','selected');}

                            let optDescending=UI.option({value:"desc"});
                            optDescending.innerHTML="Descending";
                            selectSortingOrder.appendChild(optDescending);
                            if(tmpTableConfig.sortingOrder=="desc"){optDescending.setAttribute('selected','selected');}

                            
                            selectSortingBy.addEventListener('change',function(){
                                tmpTableConfig.sortingBy=selectSortingBy.value;
                            });
                            selectSortingOrder.addEventListener('change',function(){
                                tmpTableConfig.sortingOrder=selectSortingOrder.value;
                            });

                        }
                        FixedHeaderUI();
                        FixedColumnsUI();
                        SearchUI();
                        SearchBuilderUI();
                        PageInfoUI();
                        PaginationUI();
                        TableSizeUI();
                        HeadingBackgroundUI();
                        SortingUI();
                    }
                    const columnsTabUI=async()=>{
                        const dateFormats=[
                            {value:"d-M-Y",text:"d-M-Y ("+await dateFormat("d-M-Y")+")"},
                            {value:"d/M/Y",text:"d/M/Y ("+await dateFormat("d/M/Y")+")"},
                            {value:"d-m-Y",text:"d-m-Y ("+await dateFormat("d-m-Y")+")"},
                            {value:"d/m/Y",text:"d/m/Y ("+await dateFormat("d/m/Y")+")"},
                            {value:"d-m-y",text:"d-m-y ("+await dateFormat("d-m-y")+")"},
                            {value:"d/m/y",text:"d/m/y ("+await dateFormat("d/m/y")+")"},
                            {value:"Y-M-d",text:"Y-M-d ("+await dateFormat("Y-M-d")+")"},
                            {value:"Y/M/d",text:"Y/M/d ("+await dateFormat("Y/M/d")+")"},
                            {value:"Y-m-d",text:"Y-m-d ("+await dateFormat("Y-m-d")+")"},
                            {value:"Y/m/d",text:"Y/m/d ("+await dateFormat("Y/m/d")+")"},
                            {value:"y-m-d",text:"y-m-d ("+await dateFormat("y-m-d")+")"},
                            {value:"y/m/d",text:"y/m/d ("+await dateFormat("y/m/d")+")"},
                            {value:"M d,Y",text:"M d,Y ("+await dateFormat("M d,Y")+")"}
                        ]
                        let columnTab=UI.div({class:"tab-pane fade", id:modalID+"-column", role:"tabpanel", "aria-labelledby":"general-column-tab"})
                        tabContent.appendChild(columnTab);

                        let row=UI.div({class:"row"});
                        columnTab.appendChild(row);

                        let col=UI.div({class:"col-12 mt-20"});
                        row.appendChild(col);

                        let table=UI.table({class:"table table-sm tblsettings-columns"})
                        col.appendChild(table);

                        let thead=UI.thead()
                        table.appendChild(thead);

                        let tbody=UI.tbody()
                        table.appendChild(tbody);
                        const theadUI=async()=>{
                            let tr=UI.tr()
                            thead.appendChild(tr);
    
                            let heading=['Column Name','Heading Name','Show/Hide','Align','Custom Search','Others'];
                            for(let item of heading){
                                let th=UI.th({class:"text-center"});
                                th.innerHTML=item;
                                tr.appendChild(th);
                            }
                        }
                        const tbodyUI =async()=>{
                            const tableValuesApply=async(target)=>{
                                try {
                                    let colName=target.getAttribute('data-col-name');
                                    let index = null;
                                    if(tmpTableConfig.header.length>0){
                                        for(let cIndex=0;cIndex<tmpTableConfig.header[0].length;cIndex++){
                                            if(tmpTableConfig.header[0][cIndex].columnName===colName){
                                                index=cIndex;
                                                break;
                                            }
                                        }
                                        if(tmpTableConfig.header[0][index]!=undefined){
                                            let tmp=tmpTableConfig.header[0][index];
            
                                            tmp.headingName=$('.tblsettings-columns tr[data-col-name="'+colName+'"] .txtHName').val();
                                            tmp.display=$('.tblsettings-columns tr[data-col-name="'+colName+'"] .lstDisplay').val();
                                            tmp.align=$('.tblsettings-columns tr[data-col-name="'+colName+'"] .lstAlign').val();
                                            tmp.searchBuilder.status=$('.tblsettings-columns tr[data-col-name="'+colName+'"] .lstSearchBuilder').val();
                                            if(tmp.others.type!=""){
                                                tmp.others.format=$('.tblsettings-columns tr[data-col-name="'+colName+'"] .lstFormat').val();
                                            }
                                            tmpTableConfig.header[0][index]=tmp;
                                        }
                                    }
                                } catch (error) {
                                    console.log(error);
                                }
                            }
                            for(let row of tmpTableConfig.header){
                                for(let col of row){
                                    let tr=UI.tr({"data-col-name":col.columnName});
                                    tbody.append(tr);
                                    //col name
                                    let tdColName=UI.td()
                                    tdColName.innerHTML=col.columnName
                                    tr.appendChild(tdColName);
                                    //header name
                                    let tdHName=UI.td()
                                    tr.appendChild(tdHName);

                                    let tdHNameInput=UI.input({type:"text", class:"form-control txtHName", value:col.headingName,"data-col-name":col.columnName});
                                    tdHName.appendChild(tdHNameInput);
                                    tdHNameInput.addEventListener('keyup',function(e){
                                        tableValuesApply(e.target)
                                    });
    
                                    //Visibility
                                    let tdDisplay=UI.td()
                                    tr.appendChild(tdDisplay);

                                    let displaySelect=UI.select({class:"form-control lstDisplay","data-col-name":col.columnName})
                                    tdDisplay.append(displaySelect);

                                    let showOpt=UI.selectOption({value:"Show"});
                                    showOpt.innerHTML="Show";
                                    displaySelect.appendChild(showOpt);
                                    if(col.display=="Show"){showOpt.setAttribute('selected','selected');}

                                    let hideOpt=UI.selectOption({value:"Hide"});
                                    hideOpt.innerHTML="Hide";
                                    displaySelect.appendChild(hideOpt);
                                    if(col.display=="Hide"){hideOpt.setAttribute('selected','selected');}
                                    displaySelect.addEventListener('change',function(e){
                                        tableValuesApply(e.target)
                                    });

                                    //Align
                                    let tdAlign=UI.td()
                                    tr.appendChild(tdAlign);

                                    let alignSelect=UI.select({class:"form-control lstAlign","data-col-name":col.columnName})
                                    tdAlign.append(alignSelect);

                                    let alignLeftOpt=UI.selectOption({value:"left"});
                                    alignLeftOpt.innerHTML="Left";
                                    alignSelect.appendChild(alignLeftOpt);
                                    if(col.align=="left"){alignLeftOpt.setAttribute('selected','selected');}

                                    let alignCenterOpt=UI.selectOption({value:"center"});
                                    alignCenterOpt.innerHTML="Center";
                                    alignSelect.appendChild(alignCenterOpt);
                                    if(col.align=="center"){alignCenterOpt.setAttribute('selected','selected');}

                                    let alignRightOpt=UI.selectOption({value:"right"});
                                    alignRightOpt.innerHTML="Right";
                                    alignSelect.appendChild(alignRightOpt);
                                    if(col.align=="right"){alignRightOpt.setAttribute('selected','selected');}
                                    alignSelect.addEventListener('change',function(e){
                                        tableValuesApply(e.target)
                                    });
                                    
                                    //Search Builder
                                    let tdSearchBuilder=UI.td()
                                    tr.appendChild(tdSearchBuilder);
                                    
                                    let searchBuilderSelect=UI.select({class:"form-control lstSearchBuilder","data-col-name":col.columnName})
                                    tdSearchBuilder.append(searchBuilderSelect);

                                    let searchBuilderEnabledOpt=UI.selectOption({value:"Enabled"});
                                    searchBuilderEnabledOpt.innerHTML="Enabled";
                                    searchBuilderSelect.appendChild(searchBuilderEnabledOpt);
                                    if(col.searchBuilder.status=="Enabled"){searchBuilderEnabledOpt.setAttribute('selected','selected');}

                                    let searchBuilderDisabledOpt=UI.selectOption({value:"Disabled"});
                                    searchBuilderDisabledOpt.innerHTML="Disabled";
                                    searchBuilderSelect.appendChild(searchBuilderDisabledOpt);
                                    if(col.searchBuilder.status=="Disabled"){searchBuilderDisabledOpt.setAttribute('selected','selected');}
                                    if(col.columnName.toString().toLowerCase()=="action"){
                                        $(searchBuilderSelect).val('Disabled').trigger('change');
                                        $(searchBuilderSelect).attr('disabled','disabled');
                                    }
                                    searchBuilderSelect.addEventListener('change',function(e){
                                        tableValuesApply(e.target)
                                    });
                                    //format
                                    let tdFormat=UI.td()
                                    tr.appendChild(tdFormat);
                                    if(col.others.type=="date"){ 
                                        let dateSelect=UI.select({class:"form-control lstFormat","data-col-name":col.columnName})
                                        tdFormat.append(dateSelect);
                                        for(let tmpOpt of dateFormats){
                                            let formatOpt=UI.selectOption({value:tmpOpt.value});
                                            formatOpt.innerHTML=tmpOpt.text;
                                            dateSelect.appendChild(formatOpt);
                                            if(col.others.format==tmpOpt.value){
                                                formatOpt.setAttribute('selected','selected');
                                            }
                                        }
                                        dateSelect.addEventListener('change',function(e){
                                            tableValuesApply(e.target)
                                        });
                                    }else if(col.others.type=="decimals"){
                                        let decimalSelect=UI.select({class:"form-control lstFormat text-sm","data-col-name":col.columnName})
                                        tdFormat.append(decimalSelect);
                                        for(let i=0;i<=5;i++){
                                            let formatOpt=UI.selectOption({value:i});
                                            formatOpt.innerHTML=i;
                                            decimalSelect.appendChild(formatOpt);
                                            if(col.others.format==i){
                                                formatOpt.setAttribute('selected','selected');
                                            }
                                        }
                                        decimalSelect.addEventListener('change',function(e){
                                            tableValuesApply(e.target)
                                        });
                                    }
                                }
                            }
                            if(tmpTableConfig.isSearchBuilderEnabled=="Disabled"){
                                $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").attr('disabled','disabled');
                                $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").val('Disabled').trigger('change');
                            }else{
                                $("table.tblsettings-columns .lstSearchBuilder:not([data-col-name='Action'])").removeAttr('disabled');
                            }
                        }
                        theadUI();
                        tbodyUI();
                    }
                    generalTabUI();
                    columnsTabUI();
                }
                const tablist=async(col)=>{
    
                    let navTabs=UI.ul({class:"nav nav-pills d-flex justify-content-center", id:modalID+"-tab", role:"tablist"});
                    col.appendChild(navTabs);

                    //general
                    let generalNavItem=UI.li({class:"nav-item", role:"presentation"});
                    navTabs.appendChild(generalNavItem);

                    let generalNavButton=UI.button({class:"nav-link active", id:modalID+"-general-tab", "data-bs-toggle":"pill", "data-bs-target":"#"+modalID+"-general", type:"button", role:"tab", "aria-controls":modalID+"-general", "aria-selected":"true"});
                    generalNavButton.innerHTML="General";
                    generalNavItem.appendChild(generalNavButton);

                    //Columns
                    let columnsNavItem=UI.li({class:"nav-item", role:"presentation"});
                    navTabs.appendChild(columnsNavItem);

                    let columnNavButton=UI.button({class:"nav-link", id:modalID+"-column-tab", "data-bs-toggle":"pill", "data-bs-target":"#"+modalID+"-column", type:"button", role:"tab", "aria-controls":modalID+"-column", "aria-selected":"true"});
                    columnNavButton.innerHTML="Columns";
                    columnsNavItem.appendChild(columnNavButton);
                }
                
                let row=UI.div({class:"row"});
                modalBody.appendChild(row);

                let col=UI.div({class:"col-12"});
                row.appendChild(col);
                tablist(col);
                tabContent(col);
            }
            const modalFooterUI=async()=>{
                let modalFooter=UI.div({class:"modal-footer"});
                modalContent.appendChild(modalFooter);

                let closeBtn=UI.button({type:"button", class:"btn btn-sm btn-outline-secondary", "data-bs-dismiss":"modal"});
                closeBtn.innerHTML="Close";
                modalFooter.appendChild(closeBtn);

                let applyBtn=UI.button({type:"button", class:"btn btn-sm btn-outline-primary"});
                applyBtn.innerHTML="Apply";
                modalFooter.appendChild(applyBtn);
                applyBtn.addEventListener('click',function(){
                    tableConfig=tmpTableConfig;
                    saveTableConfig();
                    pplDataTable.reinitialize();
                });
            }
            modalHeaderUI();
            modalBodyUI();
            modalFooterUI();
            $(modal).modal('show')
        }
        const initDataTable=async()=>{
            const buttons=()=>{ 
                let returnVal=[];
                //page Length
                if(options.buttons.indexOf('pageLength')>=0){
                    returnVal.push({extend: 'pageLength',className:"btnDTExportOptions btn "+options.buttonColorClass})
                }
                //copy
                if(options.buttons.indexOf('copy')>=0 && parseInt(options.permissions.copy)==1){
                    returnVal.push({
                        extend: 'copy',
                        text:'<i class="fa-regular fa-copy"></i>',
                        titleAttr:"copy",
                        className:"btnDTExportOptions btn "+options.buttonColorClass,
                        footer: true,
                        title: "",
                        action: DataTableExportOption,
                        exportOptions: {columns: "thead th:not(.noExport)"}
                    });
                }
                //csv
                if(options.buttons.indexOf('csv')>=0 && parseInt(options.permissions.csv)==1){
                    returnVal.push({
                        extend: 'csv',
                        text:'<i class="fa-solid fa-file-csv"></i>',
                        titleAttr:"Save as csv",
                        className:"btnDTExportOptions btn "+options.buttonColorClass,
                        footer: true,
                        title: "",
                        action: DataTableExportOption,
                        exportOptions: {columns: "thead th:not(.noExport)"}
                    });
                }
                //excel
                if(options.buttons.indexOf('excel')>=0 && parseInt(options.permissions.excel)==1){
                    returnVal.push({
                        extend: 'excel',
                        text:'<i class="fa-solid fa-file-excel"></i>',
                        titleAttr:"Save as excel",
                        className:"btnDTExportOptions btn "+options.buttonColorClass,
                        footer: true,
                        title: "",
                        action: DataTableExportOption,
                        exportOptions: {columns: "thead th:not(.noExport)"}
                    });
                }
                //pdf
                if(options.buttons.indexOf('pdf')>=0 && parseInt(options.permissions.pdf)==1){
                    returnVal.push({
                        extend: 'pdf',
                        text:'<i class="fa-solid fa-file-pdf"></i>',
                        titleAttr:"Save as pdf",
                        className:"btnDTExportOptions btn "+options.buttonColorClass,
                        footer: true,
                        title: "",
                        action: DataTableExportOption,
                        exportOptions: {columns: "thead th:not(.noExport)"}
                    });
                }
                //print
                if(options.buttons.indexOf('print')>=0 && parseInt(options.permissions.print)==1){
                    returnVal.push({
                        extend: 'print',
                        text:'<i class="fa-solid fa-print"></i>',
                        titleAttr:"print",
                        className:"btnDTExportOptions btn "+options.buttonColorClass,
                        footer: true,
                        title: "",
                        action: DataTableExportOption,
                        exportOptions: {columns: "thead th:not(.noExport)"}
                    });
                }
                //settings
                if(options.buttons.indexOf('settings')>=0){
                    returnVal.push({
                        text: '<i class="fa-solid fa-cog"></i>',
                        titleAttr:"Settings",
                        className:"btnDTExportOptions btn "+options.buttonColorClass,
                        init: (api, node, config) =>{$(node).removeClass('btn-secondary')},
                        footer: true,
                        title: "",
                        action: function (e, dt, node, config) {
                            generateSettingsUI();
                        }
                    });
                }
                return returnVal;
            }
            tableConfig.sortingBy=isNaN(parseInt(tableConfig.sortingBy))==false?parseInt(tableConfig.sortingBy):0;
            $(tblView).find('thead tr').removeAttr('class').addClass(tableConfig.background);
            let formats={};
            let columnDefs=[];
            let colIndex=0;
            let columnIndexMap={};
            let searchBuilderEnable=[];
            try {
                if(tableConfig.header.length>0){
                    for(column of tableConfig.header[0]){
                        let colOpts={
                            targets:colIndex,
                            visible:column.display=="Show",
                            className:"dt-"+column.align
                        }
                        columnDefs.push(colOpts);
                        columnIndexMap[column.headingName]=colIndex;
                        formats[colIndex]=column.others
                        if(tableConfig.isSearchBuilderEnabled=="Enabled"){
                            if(column.searchBuilder!=undefined && column.searchBuilder.status=="Enabled" && column.columnName.toString().toLowerCase()!="action"){
                                colOpts.searchBuilder=colOpts.searchBuilder==undefined?{}:colOpts.searchBuilder;
                                colOpts.searchBuilder['type']=column.searchBuilder.type;
                            }
                        }
                        if(column.columnName.toString().toLowerCase()!="action" && column.searchBuilder.status=="Enabled"){
                            searchBuilderEnable.push(colIndex);
                        }
                        colIndex++;
                    }
                }
            } catch (error) {
                console.log(error);
            }
            let ajax=options.ajax;
            ajax.data=ajax.data!=undefined?ajax.data:{};
            ajax.data.formats=JSON.stringify(formats);
            ajax.data.columnIndexMap=JSON.stringify(columnIndexMap);
            let dtOptions={
                processing: options.processing,
                serverSide: options.serverSide,
                pageLength: tableConfig.pageLength,
                lengthMenu: [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "All"]],
                ajax: ajax,
                layout: {
                    topStart: { buttons: buttons() }
                },
                deferRender: true,
                responsive:options.responsive,
                order:[tableConfig.sortingBy,tableConfig.sortingOrder],
                drawCallback: options.drawCallback,
                createdRow: options.createdRow,
                footerCallback: options.footerCallback,
                headerCallback: options.headerCallback,
                initComplete: options.initComplete,
                preDrawCallback: options.preDrawCallback,
                rowCallback: options.rowCallback,
                searching: tableConfig.isSearchEnabled === "Enabled",
                bInfo: tableConfig.isPageInfoEnabled === "Enabled",
                paging: tableConfig.isPaginationEnabled === "Enabled",
                fixedHeader:false ,
                scrollX:false,
                columnDefs:columnDefs,
            }
            if(tableConfig.isSearchBuilderEnabled=="Enabled"){
                dtOptions.layout=dtOptions.layout==undefined?{}:dtOptions.layout;
                dtOptions.layout.top1='searchBuilder'

                dtOptions.searchBuilder={columns:searchBuilderEnable};
                dtOptions.language=dtOptions.language==undefined?{}:dtOptions.language;
                dtOptions.language.searchBuilder={
                    add:(tableConfig.searchBuilderText!=undefined && tableConfig.searchBuilderText!="")?tableConfig.searchBuilderText:"Custom Filter",
                    title: (tableConfig.searchBuilderLabel!=undefined && tableConfig.searchBuilderLabel!="")?tableConfig.searchBuilderLabel:"",  // This removes the "Custom Search Builder" label
                }
            }
            if(options.dom!=undefined && options.dom!=null && options.dom!=""){
                dtOptions.dom=options.dom;
            }
            if(tableConfig.isFixedHeading=="Enabled"){
                dtOptions.fixedHeader= true;
                dtOptions.scrollCollapse= true;
                dtOptions.scrollY= tableConfig.height+"px";
            }
            if(tableConfig.isFixedColumns=="Enabled"){
                dtOptions.scrollX= true;
                dtOptions.scrollCollapse= true;
                dtOptions.responsive=false,
                dtOptions.fixedColumns= {
                    leftColumns:tableConfig.fixedColumns.left,
                    rightColumns:tableConfig.fixedColumns.right
                };
            }
            tblDTTable = $(tblView).dataTable(dtOptions);
        }
        
        const init=async()=>{
            await getTableConfig();
            await generateTableUI();
            initDataTable();
        }
        const pplDataTable={
            destroy:async()=>{
                if(tblDTTable!=null){
                    tblDTTable.DataTable().destroy();
                }
                tblDTTable=null;
                tblView=null;
                $(root).html('');
            },
            reinitialize:async()=>{
                if(tblDTTable!=null){
                    tblDTTable.DataTable().destroy();
                }
                tblDTTable=null;
                tblView=null;
                $(root).html('');
                await generateTableUI();
                initDataTable();
            },
            ajaxReload:async()=>{
                tblDTTable.DataTable().ajax.reload();
            }
        }
        init();
        return pplDataTable;
    }
}(jQuery));
