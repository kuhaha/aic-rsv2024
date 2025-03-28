function datetime_local() {
    const input = document.getElementById("datetime");
    const value = input.value;
  
    if (!value) return;
  
    const date = new Date(value);
  
    // 日本時間にするための調整
    date.setMinutes(date.getMinutes() + date.getTimezoneOffset() + 540); // +540分でUTC+9（日本時間）に調整
  
    // 分を10分単位で切り上げ
    const minutes = date.getMinutes();
    const roundedMinutes = Math.ceil(minutes / 10) * 10;
    date.setMinutes(roundedMinutes);
  
    // 年、月、日、時、分を取得してフォーマット
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutesStr = String(date.getMinutes()).padStart(2, '0');
  
    // 日本時間のdatetime-local形式に設定
    input.value = `${year}-${month}-${day}T${hours}:${minutesStr}`;
  }
  