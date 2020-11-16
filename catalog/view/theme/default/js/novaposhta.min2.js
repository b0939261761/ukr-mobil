const novaPoshta = (() => {
  let cities = [];

  const httpNovaPoshta = async (modelName, calledMethod, methodProperties = {}) => {
    const body = JSON.stringify({
      apiKey: '46319c903bb2cce0d0d25e6542bb5575',
      modelName,
      calledMethod,
      methodProperties
    });
    const url = 'https://api.novaposhta.ua/v2.0/json/';
    const response = await fetch(url, { method: 'POST', body });
    const data = await response.json();
    if (data && data.success) return data.data;
    console.error('NovaPoshta', data.errors);
    return [];
  }

  const getCities = async area => {
    if (!cities.length) cities = await httpNovaPoshta('Address', 'getCities');
    return cities.filter(el => el.Area === area);
  }

  return {
    getRegions: () => httpNovaPoshta('Address', 'getAreas'),
    getCities,
    getWarehouses: ref => httpNovaPoshta('AddressGeneral', 'getWarehouses', { CityRef: ref })
  }
})();

window.addEventListener('load', () => {
  const region = document.getElementById('npRegion');
  const city = document.getElementById('npCity');
  const warehouse = document.getElementById('npWarehouse');

  if (!region || !city || !warehouse) return console.error('Новая почта: нет HTML элементов');

  const setCities = async value => {
    city.options.length = 1;
    city.selectedIndex = 0;
    warehouse.options.length = 1;
    warehouse.selectedIndex = 0;

    if (!value) return;
    const cities = await novaPoshta.getCities(value);
    const refSelected = city.dataset.selectedValue;
    cities.forEach(el => city.append(new Option(el.Description, el.Ref, false, el.Ref === refSelected)));
    setWarehouses(city.value);
  }

  region.addEventListener('change', evt => setCities(evt.target.value));

  // -------------------------------------------------------------

  const setWarehouses = async value => {
    warehouse.options.length = 1;
    warehouse.selectedIndex = 0

    if (!value) return;
    const warehouses = await novaPoshta.getWarehouses(value);
    const refSelected = warehouse.dataset.selectedValue;
    warehouses.forEach(el => warehouse.append(
      new Option(el.Description, el.Ref, false, el.Ref === refSelected)
    ));
  }

  city.addEventListener('change', evt => setWarehouses(evt.target.value));

  // -------------------------------------------------------------

  (async () => {
    const regions = await novaPoshta.getRegions();
    const refSelected = region.dataset.selectedValue;
    regions.forEach(el => region.append(new Option(el.Description, el.Ref, false, el.Ref === refSelected)));
    setCities(region.value);
  })();
});
