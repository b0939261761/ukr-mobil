window.newPostInit = async (blockRegion, blockCity, blockWarehouse) => {
  const newPost = (() => {
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
      console.error('Нова Пошта', data.errors);
      return [];
    };

    const getCities = async area => {
      if (!cities.length) cities = await httpNovaPoshta('Address', 'getCities');
      return cities.filter(el => el.Area === area);
    };

    return {
      getRegions: () => httpNovaPoshta('Address', 'getAreas'),
      getCities,
      getWarehouses: ref => httpNovaPoshta('AddressGeneral', 'getWarehouses', { CityRef: ref })
    };
  })();

  // ==========================================================================

  const createItem = ({ Description, Ref }, selectedValue) => new Option(
    Description, Ref, false, Ref === selectedValue
  );

  const region = blockRegion.querySelector('select');
  const city = blockCity.querySelector('select');
  const warehouse = blockWarehouse.querySelector('select');

  // ==========================================================================

  const setWarehouses = async value => {
    warehouse.options.length = 1;
    warehouse.selectedIndex = 0;

    if (!value) return;
    const warehouses = await newPost.getWarehouses(value);
    warehouses.forEach(item => warehouse.append(createItem(item, warehouse.dataset.selectedValue)));
  };

  city.addEventListener('change', ({ target }) => setWarehouses(target.value));

  // ==========================================================================

  const setCities = async value => {
    city.options.length = 1;
    city.selectedIndex = 0;
    warehouse.options.length = 1;
    warehouse.selectedIndex = 0;

    if (!value) return;
    const cities = await newPost.getCities(value);
    cities.forEach(item => city.append(createItem(item, city.dataset.selectedValue)));
    setWarehouses(city.value);
  };

  region.addEventListener('change', ({ target }) => setCities(target.value));

  // ==========================================================================

  const regions = await newPost.getRegions();
  regions.forEach(item => region.append(createItem(item, region.dataset.selectedValue)));
  setCities(region.value);
};
