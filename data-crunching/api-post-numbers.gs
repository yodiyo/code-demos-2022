/**
 * Google Script
 *
 * Capture data from API and output to Google Sheet
 *
 * @package    boomerang_api
 *
 * @link      https://docs.google.com/spreadsheets/[LINK]
 *
 * @author     Yorick Brown
 */


function callBoomerageAPIDev() {
  
  const siteUrl = "https://example.com",
      apiEndpoint = "api/v1/totals?key=35328fcd1b8cf9e101fc0e398de0be08";
  
  const ui = SpreadsheetApp.getUi();
  const sheet = SpreadsheetApp.getActive().getSheetByName('Test');
  
  ui.alert( 'UPDATING. TO CONTINUE, PRESS OK' ); // UPDATE ALERT
  
  try {
    
    let response = UrlFetchApp.fetch(siteUrl + '/en-gb/blog/' + apiEndpoint + '&query=sites');
    
    let data = JSON.parse(response);
    
    // get site locales using path
    const locales =  data['sites'];
    
    // loop through sites
    for (let i = 0; i < locales.length; i++) {
      try {
        // url for api endpoints on each site
        let apiUrl = siteUrl + locales[i] + apiEndpoint;
        response = UrlFetchApp.fetch([apiUrl]);
        data = JSON.parse(response);
        Logger.log(locales[i]);
        Logger.log(data);
        // reset column number
        let numberColumns = 0;
        
        //loop through data properties
        for(let a in data) {
          
          // set row column
          numberColumns += 1;      
          let setRow = i+2;
          
          // filter sites prop
          if ( a !== "sites" ){
            //Logger.log('values: ', a, data[a], setRow, numberColumns);
            // make properties headings uppercase
            let headingUppercase = a.toUpperCase();
            // publish headings and data to sheet
            sheet.getRange(1, numberColumns).setValue(headingUppercase);
            sheet.getRange(setRow, numberColumns).setValue(data[a]);
            // add locales to first column
          } else if ( a === "sites"){
            // remove /blog/ from string
            let localesTrim = locales[i].replace('\/', '').replace('\/blog\/', '');
            sheet.getRange(1, 1).setValue("SITES");
            sheet.getRange(setRow, 1).setValue(localesTrim);
          }
        }     
      } catch (err){
        sheet.getRange(sheet.getLastRow()+i+1, 2).setValue("Error " + locales[i] + ": " + err.name + "\n" + err.message );      
      }
    };
    //Logger.log(locales.length, 'locales: ', locales);  
  } catch (err){
    sheet.getRange(sheet.getLastRow()+2, 2).setValue("Error getting locales: " + err.name + "\n" + err.message );
  }
  
  ui.alert( 'UPDATE COMPLETED' );
}
