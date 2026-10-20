(function () {
    'use strict';
    let dataset;
    const generations = new Map();
    function data() {
        if (!dataset) dataset = fetch('../controller/address-catalog.php?v=20260913-utf8').then(r => {
            if (!r.ok) throw new Error('Không tải được danh sách địa chỉ. Vui lòng mở lại form.');
            return r.json();
        }).catch(error => { dataset = null; throw error; });
        return dataset;
    }
    function fill(select, rows, placeholder, selected) {
        select.replaceChildren(new Option(placeholder, ''));
        rows.forEach(row => select.add(new Option(row.name, row.name)));
        if (selected && !rows.some(row => row.name === selected)) {
            select.add(new Option(selected + ' (địa chỉ đã lưu)', selected));
        }
        select.value = selected || '';
    }
    window.UniBookAddress = {
        format: address => [address.diachi_chitiet, address.xa, address.huyen, address.thanhpho].filter(Boolean).join(', '),
        async load(suffix, cityName = '', districtName = '', wardName = '') {
            const city = document.getElementById('city' + suffix);
            const ward = document.getElementById('ward' + suffix);
            const district = document.getElementById('district' + suffix);
            const form = city.closest('form');
            const save = form.querySelector('[type="submit"]');
            let message = form.querySelector('.address-help');
            if (!message) {
                message = document.createElement('p');
                message.className = 'address-help';
                message.setAttribute('role', 'status');
                city.before(message);
            }
            const generation = (generations.get(suffix) || 0) + 1;
            generations.set(suffix, generation);
            save.disabled = city.disabled = ward.disabled = true;
            district.required = false;
            district.hidden = true;
            district.style.display = 'none';
            district.replaceChildren(new Option(districtName, districtName));
            message.textContent = 'Đang tải địa chỉ…';
            try {
                const provinces = await data();
                if (generations.get(suffix) !== generation) return;
                fill(city, provinces, 'Chọn tỉnh/thành phố', cityName);
                function wards(selected = '') {
                    const province = provinces.find(p => p.name === city.value);
                    fill(ward, province ? province.wards : [], 'Chọn phường/xã/đặc khu', selected);
                    ward.disabled = !city.value;
                }
                city.onchange = () => { district.replaceChildren(new Option('', '')); wards(); };
                wards(wardName);
                city.disabled = save.disabled = false;
                message.textContent = districtName
                    ? 'Địa chỉ cũ được giữ nguyên. Khi đổi tỉnh/thành, hãy chọn phường/xã hiện hành.'
                    : 'Chọn tỉnh/thành và phường/xã theo danh mục sau sáp nhập 2025.';
            } catch (error) {
                if (generations.get(suffix) === generation) message.textContent = error.message;
            }
        }
    };
})();
